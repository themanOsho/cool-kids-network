<?php declare(strict_types=1);

namespace PhpParser\Node\Expr;

use PhpParser\Node\Expr;

class Variable extends Expr {
	/** @var string|Expr Name */
	public $name;

	/**
	 * Constructs a variable node.
	 *
	 * @param string|Expr          $name Name
	 * @param array<string, mixed> $attributes Additional attributes
	 */
	public function __construct( $name, array $attributes = array() ) {
		$this->attributes = $attributes;
		$this->name       = $name;
	}

	public function getSubNodeNames(): array {
		return array( 'name' );
	}

	public function getType(): string {
		return 'Expr_Variable';
	}
}
