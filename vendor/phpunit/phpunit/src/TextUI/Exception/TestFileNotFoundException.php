<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TextUI;

use function sprintf;
use RuntimeException;

/**
 * @internal This interface is not covered by the backward compatibility promise for PHPUnit
 */
final class TestFileNotFoundException extends RuntimeException implements Exception {

	public function __construct( string $path ) {
		parent::__construct(
			sprintf(
				'Test file "%s" not found',
				$path,
			),
		);
	}
}
