<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Util;

use function array_unshift;
use function defined;
use function in_array;
use function is_file;
use function realpath;
use function sprintf;
use function strpos;
use PHPUnit\Framework\Exception;
use PHPUnit\Framework\SyntheticError;
use Throwable;

/**
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class Filter {

	/**
	 * @throws Exception
	 */
	public static function getFilteredStacktrace( Throwable $t ): string {
		$filteredStacktrace = '';

		if ( $t instanceof SyntheticError ) {
			$eTrace = $t->getSyntheticTrace();
			$eFile  = $t->getSyntheticFile();
			$eLine  = $t->getSyntheticLine();
		} elseif ( $t instanceof Exception ) {
			$eTrace = $t->getSerializableTrace();
			$eFile  = $t->getFile();
			$eLine  = $t->getLine();
		} else {
			if ( $t->getPrevious() ) {
				$t = $t->getPrevious();
			}

			$eTrace = $t->getTrace();
			$eFile  = $t->getFile();
			$eLine  = $t->getLine();
		}

		if ( ! self::frameExists( $eTrace, $eFile, $eLine ) ) {
			array_unshift(
				$eTrace,
				array(
					'file' => $eFile,
					'line' => $eLine,
				),
			);
		}

		$prefix      = defined( '__PHPUNIT_PHAR_ROOT__' ) ? __PHPUNIT_PHAR_ROOT__ : false;
		$excludeList = new ExcludeList();

		foreach ( $eTrace as $frame ) {
			if ( self::shouldPrintFrame( $frame, $prefix, $excludeList ) ) {
				$filteredStacktrace .= sprintf(
					"%s:%s\n",
					$frame['file'],
					$frame['line'] ?? '?',
				);
			}
		}

		return $filteredStacktrace;
	}

	private static function shouldPrintFrame( array $frame, $prefix, ExcludeList $excludeList ): bool {
		if ( ! isset( $frame['file'] ) ) {
			return false;
		}

		$file              = $frame['file'];
		$fileIsNotPrefixed = $prefix === false || strpos( $file, $prefix ) !== 0;

		// @see https://github.com/sebastianbergmann/phpunit/issues/4033
		if ( isset( $GLOBALS['_SERVER']['SCRIPT_NAME'] ) ) {
			$script = realpath( $GLOBALS['_SERVER']['SCRIPT_NAME'] );
		} else {
			$script = '';
		}

		return is_file( $file ) &&
				self::fileIsExcluded( $file, $excludeList ) &&
				$fileIsNotPrefixed &&
				$file !== $script;
	}

	private static function fileIsExcluded( string $file, ExcludeList $excludeList ): bool {
		return ( empty( $GLOBALS['__PHPUNIT_ISOLATION_EXCLUDE_LIST'] ) ||
				! in_array( $file, $GLOBALS['__PHPUNIT_ISOLATION_EXCLUDE_LIST'], true ) ) &&
				! $excludeList->isExcluded( $file );
	}

	private static function frameExists( array $trace, string $file, int $line ): bool {
		foreach ( $trace as $frame ) {
			if ( isset( $frame['file'], $frame['line'] ) && $frame['file'] === $file && $frame['line'] === $line ) {
				return true;
			}
		}

		return false;
	}
}
