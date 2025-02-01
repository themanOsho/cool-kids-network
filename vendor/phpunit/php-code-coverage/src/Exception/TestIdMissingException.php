<?php declare(strict_types=1);
/*
 * This file is part of phpunit/php-code-coverage.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace SebastianBergmann\CodeCoverage;

use RuntimeException;

final class TestIdMissingException extends RuntimeException implements Exception {

	public function __construct() {
		parent::__construct( 'Test ID is missing' );
	}
}
