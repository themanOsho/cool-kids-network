<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TextUI\XmlConfiguration;

use function count;
use function iterator_count;
use Countable;
use Iterator;

/**
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 *
 * @template-implements Iterator<int, Group>
 */
final class GroupCollectionIterator implements Countable, Iterator {

	/**
	 * @var Group[]
	 */
	private $groups;

	/**
	 * @var int
	 */
	private $position;

	public function __construct( GroupCollection $groups ) {
		$this->groups = $groups->asArray();
	}

	public function count(): int {
		return iterator_count( $this );
	}

	public function rewind(): void {
		$this->position = 0;
	}

	public function valid(): bool {
		return $this->position < count( $this->groups );
	}

	public function key(): int {
		return $this->position;
	}

	public function current(): Group {
		return $this->groups[ $this->position ];
	}

	public function next(): void {
		++$this->position;
	}
}
