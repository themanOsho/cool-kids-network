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
final class CoveragePhpToReport extends LogToReportMigration {

	protected function forType(): string {
		return 'coverage-php';
	}

	protected function toReportFormat( DOMElement $logNode ): DOMElement {
		$php = $logNode->ownerDocument->createElement( 'php' );
		$php->setAttribute( 'outputFile', $logNode->getAttribute( 'target' ) );

		return $php;
	}
}
