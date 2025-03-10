<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TextUI\XmlConfiguration\CodeCoverage\Filter;

use function count;
use function iterator_count;
use Countable;
use Iterator;

/**
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 *
 * @template-implements Iterator<int, Directory>
 */
final class DirectoryCollectionIterator implements Countable, Iterator {

	/**
	 * @var Directory[]
	 */
	private $directories;

	/**
	 * @var int
	 */
	private $position;

	public function __construct( DirectoryCollection $directories ) {
		$this->directories = $directories->asArray();
	}

	public function count(): int {
		return iterator_count( $this );
	}

	public function rewind(): void {
		$this->position = 0;
	}

	public function valid(): bool {
		return $this->position < count( $this->directories );
	}

	public function key(): int {
		return $this->position;
	}

	public function current(): Directory {
		return $this->directories[ $this->position ];
	}

	public function next(): void {
		++$this->position;
	}
}
