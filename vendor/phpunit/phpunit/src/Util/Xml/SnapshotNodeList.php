<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Util\Xml;

use function count;
use ArrayIterator;
use Countable;
use DOMNode;
use DOMNodeList;
use IteratorAggregate;

/**
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 *
 * @template-implements IteratorAggregate<int, DOMNode>
 */
final class SnapshotNodeList implements Countable, IteratorAggregate {

	/**
	 * @var DOMNode[]
	 */
	private $nodes = array();

	public static function fromNodeList( DOMNodeList $list ): self {
		$snapshot = new self();

		foreach ( $list as $node ) {
			$snapshot->nodes[] = $node;
		}

		return $snapshot;
	}

	public function count(): int {
		return count( $this->nodes );
	}

	public function getIterator(): ArrayIterator {
		return new ArrayIterator( $this->nodes );
	}
}
