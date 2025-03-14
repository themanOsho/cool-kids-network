<?php declare(strict_types=1);
/*
 * This file is part of phpunit/php-code-coverage.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace SebastianBergmann\CodeCoverage\Driver;

use const pcov\inclusive;
use function array_intersect;
use function extension_loaded;
use function pcov\clear;
use function pcov\collect;
use function pcov\start;
use function pcov\stop;
use function pcov\waiting;
use function phpversion;
use SebastianBergmann\CodeCoverage\Filter;
use SebastianBergmann\CodeCoverage\RawCodeCoverageData;

/**
 * @internal This class is not covered by the backward compatibility promise for phpunit/php-code-coverage
 */
final class PcovDriver extends Driver {

	/**
	 * @var Filter
	 */
	private $filter;

	/**
	 * @throws PcovNotAvailableException
	 */
	public function __construct( Filter $filter ) {
		if ( ! extension_loaded( 'pcov' ) ) {
			throw new PcovNotAvailableException();
		}

		$this->filter = $filter;
	}

	public function start(): void {
		start();
	}

	public function stop(): RawCodeCoverageData {
		stop();

		$filesToCollectCoverageFor = waiting();
		$collected                 = array();

		if ( $filesToCollectCoverageFor ) {
			if ( ! $this->filter->isEmpty() ) {
				$filesToCollectCoverageFor = array_intersect( $filesToCollectCoverageFor, $this->filter->files() );
			}

			$collected = collect( inclusive, $filesToCollectCoverageFor );

			clear();
		}

		return RawCodeCoverageData::fromXdebugWithoutPathCoverage( $collected );
	}

	public function nameAndVersion(): string {
		return 'PCOV ' . phpversion( 'pcov' );
	}
}
