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

/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 */
final class IsTrue extends Constraint {

	/**
	 * Returns a string representation of the constraint.
	 */
	public function toString(): string {
		return 'is true';
	}

	/**
	 * Evaluates the constraint for parameter $other. Returns true if the
	 * constraint is met, false otherwise.
	 *
	 * @param mixed $other value or object to evaluate
	 */
	protected function matches( $other ): bool {
		return $other === true;
	}
}
