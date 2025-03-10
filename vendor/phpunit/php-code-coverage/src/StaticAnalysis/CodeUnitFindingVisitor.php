<?php declare(strict_types=1);
/*
 * This file is part of phpunit/php-code-coverage.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace SebastianBergmann\CodeCoverage\StaticAnalysis;

use function assert;
use function implode;
use function rtrim;
use function trim;
use PhpParser\Node;
use PhpParser\Node\ComplexType;
use PhpParser\Node\Identifier;
use PhpParser\Node\IntersectionType;
use PhpParser\Node\Name;
use PhpParser\Node\NullableType;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Enum_;
use PhpParser\Node\Stmt\Function_;
use PhpParser\Node\Stmt\Interface_;
use PhpParser\Node\Stmt\Trait_;
use PhpParser\Node\UnionType;
use PhpParser\NodeAbstract;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitorAbstract;
use SebastianBergmann\Complexity\CyclomaticComplexityCalculatingVisitor;

/**
 * @internal This class is not covered by the backward compatibility promise for phpunit/php-code-coverage
 */
final class CodeUnitFindingVisitor extends NodeVisitorAbstract {

	/**
	 * @psalm-var array<string,array{name: string, namespacedName: string, namespace: string, startLine: int, endLine: int, methods: array<string,array{methodName: string, signature: string, visibility: string, startLine: int, endLine: int, ccn: int}>}>
	 */
	private $classes = array();

	/**
	 * @psalm-var array<string,array{name: string, namespacedName: string, namespace: string, startLine: int, endLine: int, methods: array<string,array{methodName: string, signature: string, visibility: string, startLine: int, endLine: int, ccn: int}>}>
	 */
	private $traits = array();

	/**
	 * @psalm-var array<string,array{name: string, namespacedName: string, namespace: string, signature: string, startLine: int, endLine: int, ccn: int}>
	 */
	private $functions = array();

	public function enterNode( Node $node ): void {
		if ( $node instanceof Class_ ) {
			if ( $node->isAnonymous() ) {
				return;
			}

			$this->processClass( $node );
		}

		if ( $node instanceof Trait_ ) {
			$this->processTrait( $node );
		}

		if ( ! $node instanceof ClassMethod && ! $node instanceof Function_ ) {
			return;
		}

		if ( $node instanceof ClassMethod ) {
			$parentNode = $node->getAttribute( 'parent' );

			if ( $parentNode instanceof Class_ && $parentNode->isAnonymous() ) {
				return;
			}

			$this->processMethod( $node );

			return;
		}

		$this->processFunction( $node );
	}

	/**
	 * @psalm-return array<string,array{name: string, namespacedName: string, namespace: string, startLine: int, endLine: int, methods: array<string,array{methodName: string, signature: string, visibility: string, startLine: int, endLine: int, ccn: int}>}>
	 */
	public function classes(): array {
		return $this->classes;
	}

	/**
	 * @psalm-return array<string,array{name: string, namespacedName: string, namespace: string, startLine: int, endLine: int, methods: array<string,array{methodName: string, signature: string, visibility: string, startLine: int, endLine: int, ccn: int}>}>
	 */
	public function traits(): array {
		return $this->traits;
	}

	/**
	 * @psalm-return array<string,array{name: string, namespacedName: string, namespace: string, signature: string, startLine: int, endLine: int, ccn: int}>
	 */
	public function functions(): array {
		return $this->functions;
	}

	/**
	 * @psalm-param ClassMethod|Function_ $node
	 */
	private function cyclomaticComplexity( Node $node ): int {
		assert( $node instanceof ClassMethod || $node instanceof Function_ );

		$nodes = $node->getStmts();

		if ( $nodes === null ) {
			return 0;
		}

		$traverser = new NodeTraverser();

		$cyclomaticComplexityCalculatingVisitor = new CyclomaticComplexityCalculatingVisitor();

		$traverser->addVisitor( $cyclomaticComplexityCalculatingVisitor );

		/* @noinspection UnusedFunctionResultInspection */
		$traverser->traverse( $nodes );

		return $cyclomaticComplexityCalculatingVisitor->cyclomaticComplexity();
	}

	/**
	 * @psalm-param ClassMethod|Function_ $node
	 */
	private function signature( Node $node ): string {
		assert( $node instanceof ClassMethod || $node instanceof Function_ );

		$signature  = ( $node->returnsByRef() ? '&' : '' ) . $node->name->toString() . '(';
		$parameters = array();

		foreach ( $node->getParams() as $parameter ) {
			assert( isset( $parameter->var->name ) );

			$parameterAsString = '';

			if ( $parameter->type !== null ) {
				$parameterAsString = $this->type( $parameter->type ) . ' ';
			}

			$parameterAsString .= '$' . $parameter->var->name;

			/* @todo Handle default values */

			$parameters[] = $parameterAsString;
		}

		$signature .= implode( ', ', $parameters ) . ')';

		$returnType = $node->getReturnType();

		if ( $returnType !== null ) {
			$signature .= ': ' . $this->type( $returnType );
		}

		return $signature;
	}

	/**
	 * @psalm-param Identifier|Name|ComplexType $type
	 */
	private function type( Node $type ): string {
		assert( $type instanceof Identifier || $type instanceof Name || $type instanceof ComplexType );

		if ( $type instanceof NullableType ) {
			return '?' . $type->type;
		}

		if ( $type instanceof UnionType ) {
			return $this->unionTypeAsString( $type );
		}

		if ( $type instanceof IntersectionType ) {
			return $this->intersectionTypeAsString( $type );
		}

		return $type->toString();
	}

	private function visibility( ClassMethod $node ): string {
		if ( $node->isPrivate() ) {
			return 'private';
		}

		if ( $node->isProtected() ) {
			return 'protected';
		}

		return 'public';
	}

	private function processClass( Class_ $node ): void {
		$name           = $node->name->toString();
		$namespacedName = $node->namespacedName->toString();

		$this->classes[ $namespacedName ] = array(
			'name'           => $name,
			'namespacedName' => $namespacedName,
			'namespace'      => $this->namespace( $namespacedName, $name ),
			'startLine'      => $node->getStartLine(),
			'endLine'        => $node->getEndLine(),
			'methods'        => array(),
		);
	}

	private function processTrait( Trait_ $node ): void {
		$name           = $node->name->toString();
		$namespacedName = $node->namespacedName->toString();

		$this->traits[ $namespacedName ] = array(
			'name'           => $name,
			'namespacedName' => $namespacedName,
			'namespace'      => $this->namespace( $namespacedName, $name ),
			'startLine'      => $node->getStartLine(),
			'endLine'        => $node->getEndLine(),
			'methods'        => array(),
		);
	}

	private function processMethod( ClassMethod $node ): void {
		$parentNode = $node->getAttribute( 'parent' );

		if ( $parentNode instanceof Interface_ ) {
			return;
		}

		assert( $parentNode instanceof Class_ || $parentNode instanceof Trait_ || $parentNode instanceof Enum_ );
		assert( isset( $parentNode->name ) );
		assert( isset( $parentNode->namespacedName ) );
		assert( $parentNode->namespacedName instanceof Name );

		$parentName           = $parentNode->name->toString();
		$parentNamespacedName = $parentNode->namespacedName->toString();

		if ( $parentNode instanceof Class_ ) {
			$storage = &$this->classes;
		} else {
			$storage = &$this->traits;
		}

		if ( ! isset( $storage[ $parentNamespacedName ] ) ) {
			$storage[ $parentNamespacedName ] = array(
				'name'           => $parentName,
				'namespacedName' => $parentNamespacedName,
				'namespace'      => $this->namespace( $parentNamespacedName, $parentName ),
				'startLine'      => $parentNode->getStartLine(),
				'endLine'        => $parentNode->getEndLine(),
				'methods'        => array(),
			);
		}

		$storage[ $parentNamespacedName ]['methods'][ $node->name->toString() ] = array(
			'methodName' => $node->name->toString(),
			'signature'  => $this->signature( $node ),
			'visibility' => $this->visibility( $node ),
			'startLine'  => $node->getStartLine(),
			'endLine'    => $node->getEndLine(),
			'ccn'        => $this->cyclomaticComplexity( $node ),
		);
	}

	private function processFunction( Function_ $node ): void {
		assert( isset( $node->name ) );
		assert( isset( $node->namespacedName ) );
		assert( $node->namespacedName instanceof Name );

		$name           = $node->name->toString();
		$namespacedName = $node->namespacedName->toString();

		$this->functions[ $namespacedName ] = array(
			'name'           => $name,
			'namespacedName' => $namespacedName,
			'namespace'      => $this->namespace( $namespacedName, $name ),
			'signature'      => $this->signature( $node ),
			'startLine'      => $node->getStartLine(),
			'endLine'        => $node->getEndLine(),
			'ccn'            => $this->cyclomaticComplexity( $node ),
		);
	}

	private function namespace( string $namespacedName, string $name ): string {
		return trim( rtrim( $namespacedName, $name ), '\\' );
	}

	private function unionTypeAsString( UnionType $node ): string {
		$types = array();

		foreach ( $node->types as $type ) {
			if ( $type instanceof IntersectionType ) {
				$types[] = '(' . $this->intersectionTypeAsString( $type ) . ')';

				continue;
			}

			$types[] = $this->typeAsString( $type );
		}

		return implode( '|', $types );
	}

	private function intersectionTypeAsString( IntersectionType $node ): string {
		$types = array();

		foreach ( $node->types as $type ) {
			$types[] = $this->typeAsString( $type );
		}

		return implode( '&', $types );
	}

	/**
	 * @psalm-param Identifier|Name $node $node
	 */
	private function typeAsString( NodeAbstract $node ): string {
		if ( $node instanceof Name ) {
			return $node->toCodeString();
		}

		return $node->toString();
	}
}
