<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Framework\MockObject;

use function sprintf;

/**
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class MatcherAlreadyRegisteredException extends \PHPUnit\Framework\Exception implements Exception {

	public function __construct( string $id ) {
		parent::__construct(
			sprintf(
				'Matcher with id <%s> is already registered',
				$id,
			),
		);
	}
}
