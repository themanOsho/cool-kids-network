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

use DOMElement;

/**
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class CoverageCrap4jToReport extends LogToReportMigration {

	protected function forType(): string {
		return 'coverage-crap4j';
	}

	protected function toReportFormat( DOMElement $logNode ): DOMElement {
		$crap4j = $logNode->ownerDocument->createElement( 'crap4j' );
		$crap4j->setAttribute( 'outputFile', $logNode->getAttribute( 'target' ) );

		$this->migrateAttributes( $logNode, $crap4j, array( 'threshold' ) );

		return $crap4j;
	}
}
