<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Framework\Constraint;

use function is_string;
use function sprintf;
use function strpos;
use function trim;
use PHPUnit\Framework\ExpectationFailedException;
use SebastianBergmann\Comparator\ComparisonFailure;
use SebastianBergmann\Comparator\Factory as ComparatorFactory;
use SebastianBergmann\RecursionContext\InvalidArgumentException;

/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 */
final class IsEqualCanonicalizing extends Constraint {

	/**
	 * @var mixed
	 */
	private $value;

	public function __construct( $value ) {
		$this->value = $value;
	}

	/**
	 * Evaluates the constraint for parameter $other.
	 *
	 * If $returnResult is set to false (the default), an exception is thrown
	 * in case of a failure. null is returned otherwise.
	 *
	 * If $returnResult is true, the result of the evaluation is returned as
	 * a boolean value instead: true in case of success, false in case of a
	 * failure.
	 *
	 * @throws ExpectationFailedException
	 */
	public function evaluate( $other, string $description = '', bool $returnResult = false ): ?bool {
		// If $this->value and $other are identical, they are also equal.
		// This is the most common path and will allow us to skip
		// initialization of all the comparators.
		if ( $this->value === $other ) {
			return true;
		}

		$comparatorFactory = ComparatorFactory::getInstance();

		try {
			$comparator = $comparatorFactory->getComparatorFor(
				$this->value,
				$other,
			);

			$comparator->assertEquals(
				$this->value,
				$other,
				0.0,
				true,
				false,
			);
		} catch ( ComparisonFailure $f ) {
			if ( $returnResult ) {
				return false;
			}

			throw new ExpectationFailedException(
				trim( $description . "\n" . $f->getMessage() ),
				$f,
			);
		}

		return true;
	}

	/**
	 * Returns a string representation of the constraint.
	 *
	 * @throws InvalidArgumentException
	 */
	public function toString(): string {
		if ( is_string( $this->value ) ) {
			if ( strpos( $this->value, "\n" ) !== false ) {
				return 'is equal to <text>';
			}

			return sprintf(
				"is equal to '%s'",
				$this->value,
			);
		}

		return sprintf(
			'is equal to %s',
			$this->exporter()->export( $this->value ),
		);
	}
}
