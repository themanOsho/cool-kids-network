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

use const PHP_EOL;
use function count;
use function defined;
use function explode;
use function max;
use function preg_replace_callback;
use function str_pad;
use function str_repeat;
use function strlen;
use function wordwrap;
use PHPUnit\Util\Color;
use SebastianBergmann\Environment\Console;

/**
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class Help {

	private const LEFT_MARGIN = '  ';

	/**
	 * @var int Number of columns required to write the longest option name to the console
	 */
	private $maxArgLength = 0;

	/**
	 * @var int Number of columns left for the description field after padding and option
	 */
	private $maxDescLength;

	/**
	 * @var bool Use color highlights for sections, options and parameters
	 */
	private $hasColor = false;

	public function __construct( ?int $width = null, ?bool $withColor = null ) {
		if ( $width === null ) {
			$width = ( new Console() )->getNumberOfColumns();
		}

		if ( $withColor === null ) {
			$this->hasColor = ( new Console() )->hasColorSupport();
		} else {
			$this->hasColor = $withColor;
		}

		foreach ( $this->elements() as $options ) {
			foreach ( $options as $option ) {
				if ( isset( $option['arg'] ) ) {
					$this->maxArgLength = max( $this->maxArgLength, isset( $option['arg'] ) ? strlen( $option['arg'] ) : 0 );
				}
			}
		}

		$this->maxDescLength = $width - $this->maxArgLength - 4;
	}

	/**
	 * Write the help file to the CLI, adapting width and colors to the console.
	 */
	public function writeToConsole(): void {
		if ( $this->hasColor ) {
			$this->writeWithColor();
		} else {
			$this->writePlaintext();
		}
	}

	private function writePlaintext(): void {
		foreach ( $this->elements() as $section => $options ) {
			print "{$section}:" . PHP_EOL;

			if ( $section !== 'Usage' ) {
				print PHP_EOL;
			}

			foreach ( $options as $option ) {
				if ( isset( $option['spacer'] ) ) {
					print PHP_EOL;
				}

				if ( isset( $option['text'] ) ) {
					print self::LEFT_MARGIN . $option['text'] . PHP_EOL;
				}

				if ( isset( $option['arg'] ) ) {
					$arg = str_pad( $option['arg'], $this->maxArgLength );
					print self::LEFT_MARGIN . $arg . ' ' . $option['desc'] . PHP_EOL;
				}
			}

			print PHP_EOL;
		}
	}

	private function writeWithColor(): void {
		foreach ( $this->elements() as $section => $options ) {
			print Color::colorize( 'fg-yellow', "{$section}:" ) . PHP_EOL;

			foreach ( $options as $option ) {
				if ( isset( $option['spacer'] ) ) {
					print PHP_EOL;
				}

				if ( isset( $option['text'] ) ) {
					print self::LEFT_MARGIN . $option['text'] . PHP_EOL;
				}

				if ( isset( $option['arg'] ) ) {
					$arg  = Color::colorize( 'fg-green', str_pad( $option['arg'], $this->maxArgLength ) );
					$arg  = preg_replace_callback(
						'/(<[^>]+>)/',
						static function ( $matches ) {
							return Color::colorize( 'fg-cyan', $matches[0] );
						},
						$arg,
					);
					$desc = explode( PHP_EOL, wordwrap( $option['desc'], $this->maxDescLength, PHP_EOL ) );

					print self::LEFT_MARGIN . $arg . ' ' . $desc[0] . PHP_EOL;

					for ( $i = 1; $i < count( $desc ); $i++ ) {
						print str_repeat( ' ', $this->maxArgLength + 3 ) . $desc[ $i ] . PHP_EOL;
					}
				}
			}

			print PHP_EOL;
		}
	}

	/**
	 * @psalm-return array<non-empty-string, non-empty-list<array{text: non-empty-string}|array{arg: non-empty-string, desc: non-empty-string}|array{spacer: ''}>>
	 */
	private function elements(): array {
		$elements = array(
			'Usage'                  => array(
				array( 'text' => 'phpunit [options] UnitTest.php' ),
				array( 'text' => 'phpunit [options] <directory>' ),
			),

			'Code Coverage Options'  => array(
				array(
					'arg'  => '--coverage-clover <file>',
					'desc' => 'Generate code coverage report in Clover XML format',
				),
				array(
					'arg'  => '--coverage-cobertura <file>',
					'desc' => 'Generate code coverage report in Cobertura XML format',
				),
				array(
					'arg'  => '--coverage-crap4j <file>',
					'desc' => 'Generate code coverage report in Crap4J XML format',
				),
				array(
					'arg'  => '--coverage-html <dir>',
					'desc' => 'Generate code coverage report in HTML format',
				),
				array(
					'arg'  => '--coverage-php <file>',
					'desc' => 'Export PHP_CodeCoverage object to file',
				),
				array(
					'arg'  => '--coverage-text=<file>',
					'desc' => 'Generate code coverage report in text format [default: standard output]',
				),
				array(
					'arg'  => '--coverage-xml <dir>',
					'desc' => 'Generate code coverage report in PHPUnit XML format',
				),
				array(
					'arg'  => '--coverage-cache <dir>',
					'desc' => 'Cache static analysis results',
				),
				array(
					'arg'  => '--warm-coverage-cache',
					'desc' => 'Warm static analysis cache',
				),
				array(
					'arg'  => '--coverage-filter <dir>',
					'desc' => 'Include <dir> in code coverage analysis',
				),
				array(
					'arg'  => '--path-coverage',
					'desc' => 'Perform path coverage analysis',
				),
				array(
					'arg'  => '--disable-coverage-ignore',
					'desc' => 'Disable annotations for ignoring code coverage',
				),
				array(
					'arg'  => '--no-coverage',
					'desc' => 'Ignore code coverage configuration',
				),
			),

			'Logging Options'        => array(
				array(
					'arg'  => '--log-junit <file>',
					'desc' => 'Log test execution in JUnit XML format to file',
				),
				array(
					'arg'  => '--log-teamcity <file>',
					'desc' => 'Log test execution in TeamCity format to file',
				),
				array(
					'arg'  => '--testdox-html <file>',
					'desc' => 'Write agile documentation in HTML format to file',
				),
				array(
					'arg'  => '--testdox-text <file>',
					'desc' => 'Write agile documentation in Text format to file',
				),
				array(
					'arg'  => '--testdox-xml <file>',
					'desc' => 'Write agile documentation in XML format to file',
				),
				array(
					'arg'  => '--reverse-list',
					'desc' => 'Print defects in reverse order',
				),
				array(
					'arg'  => '--no-logging',
					'desc' => 'Ignore logging configuration',
				),
			),

			'Test Selection Options' => array(
				array(
					'arg'  => '--list-suites',
					'desc' => 'List available test suites',
				),
				array(
					'arg'  => '--testsuite <name>',
					'desc' => 'Filter which testsuite to run',
				),
				array(
					'arg'  => '--list-groups',
					'desc' => 'List available test groups',
				),
				array(
					'arg'  => '--group <name>',
					'desc' => 'Only runs tests from the specified group(s)',
				),
				array(
					'arg'  => '--exclude-group <name>',
					'desc' => 'Exclude tests from the specified group(s)',
				),
				array(
					'arg'  => '--covers <name>',
					'desc' => 'Only runs tests annotated with "@covers <name>"',
				),
				array(
					'arg'  => '--uses <name>',
					'desc' => 'Only runs tests annotated with "@uses <name>"',
				),
				array(
					'arg'  => '--list-tests',
					'desc' => 'List available tests',
				),
				array(
					'arg'  => '--list-tests-xml <file>',
					'desc' => 'List available tests in XML format',
				),
				array(
					'arg'  => '--filter <pattern>',
					'desc' => 'Filter which tests to run',
				),
				array(
					'arg'  => '--test-suffix <suffixes>',
					'desc' => 'Only search for test in files with specified suffix(es). Default: Test.php,.phpt',
				),
			),

			'Test Execution Options' => array(
				array(
					'arg'  => '--dont-report-useless-tests',
					'desc' => 'Do not report tests that do not test anything',
				),
				array(
					'arg'  => '--strict-coverage',
					'desc' => 'Be strict about @covers annotation usage',
				),
				array(
					'arg'  => '--strict-global-state',
					'desc' => 'Be strict about changes to global state',
				),
				array(
					'arg'  => '--disallow-test-output',
					'desc' => 'Be strict about output during tests',
				),
				array(
					'arg'  => '--disallow-resource-usage',
					'desc' => 'Be strict about resource usage during small tests',
				),
				array(
					'arg'  => '--enforce-time-limit',
					'desc' => 'Enforce time limit based on test size',
				),
				array(
					'arg'  => '--default-time-limit <sec>',
					'desc' => 'Timeout in seconds for tests without @small, @medium or @large',
				),
				array(
					'arg'  => '--disallow-todo-tests',
					'desc' => 'Disallow @todo-annotated tests',
				),
				array( 'spacer' => '' ),

				array(
					'arg'  => '--process-isolation',
					'desc' => 'Run each test in a separate PHP process',
				),
				array(
					'arg'  => '--globals-backup',
					'desc' => 'Backup and restore $GLOBALS for each test',
				),
				array(
					'arg'  => '--static-backup',
					'desc' => 'Backup and restore static attributes for each test',
				),
				array( 'spacer' => '' ),

				array(
					'arg'  => '--colors <flag>',
					'desc' => 'Use colors in output ("never", "auto" or "always")',
				),
				array(
					'arg'  => '--columns <n>',
					'desc' => 'Number of columns to use for progress output',
				),
				array(
					'arg'  => '--columns max',
					'desc' => 'Use maximum number of columns for progress output',
				),
				array(
					'arg'  => '--stderr',
					'desc' => 'Write to STDERR instead of STDOUT',
				),
				array(
					'arg'  => '--stop-on-defect',
					'desc' => 'Stop execution upon first not-passed test',
				),
				array(
					'arg'  => '--stop-on-error',
					'desc' => 'Stop execution upon first error',
				),
				array(
					'arg'  => '--stop-on-failure',
					'desc' => 'Stop execution upon first error or failure',
				),
				array(
					'arg'  => '--stop-on-warning',
					'desc' => 'Stop execution upon first warning',
				),
				array(
					'arg'  => '--stop-on-risky',
					'desc' => 'Stop execution upon first risky test',
				),
				array(
					'arg'  => '--stop-on-skipped',
					'desc' => 'Stop execution upon first skipped test',
				),
				array(
					'arg'  => '--stop-on-incomplete',
					'desc' => 'Stop execution upon first incomplete test',
				),
				array(
					'arg'  => '--fail-on-incomplete',
					'desc' => 'Treat incomplete tests as failures',
				),
				array(
					'arg'  => '--fail-on-risky',
					'desc' => 'Treat risky tests as failures',
				),
				array(
					'arg'  => '--fail-on-skipped',
					'desc' => 'Treat skipped tests as failures',
				),
				array(
					'arg'  => '--fail-on-warning',
					'desc' => 'Treat tests with warnings as failures',
				),
				array(
					'arg'  => '-v|--verbose',
					'desc' => 'Output more verbose information',
				),
				array(
					'arg'  => '--debug',
					'desc' => 'Display debugging information',
				),
				array( 'spacer' => '' ),

				array(
					'arg'  => '--repeat <times>',
					'desc' => 'Runs the test(s) repeatedly',
				),
				array(
					'arg'  => '--teamcity',
					'desc' => 'Report test execution progress in TeamCity format',
				),
				array(
					'arg'  => '--testdox',
					'desc' => 'Report test execution progress in TestDox format',
				),
				array(
					'arg'  => '--testdox-group',
					'desc' => 'Only include tests from the specified group(s)',
				),
				array(
					'arg'  => '--testdox-exclude-group',
					'desc' => 'Exclude tests from the specified group(s)',
				),
				array(
					'arg'  => '--no-interaction',
					'desc' => 'Disable TestDox progress animation',
				),
				array(
					'arg'  => '--printer <printer>',
					'desc' => 'TestListener implementation to use',
				),
				array( 'spacer' => '' ),

				array(
					'arg'  => '--order-by <order>',
					'desc' => 'Run tests in order: default|defects|duration|no-depends|random|reverse|size',
				),
				array(
					'arg'  => '--random-order-seed <N>',
					'desc' => 'Use a specific random seed <N> for random order',
				),
				array(
					'arg'  => '--cache-result',
					'desc' => 'Write test results to cache file',
				),
				array(
					'arg'  => '--do-not-cache-result',
					'desc' => 'Do not write test results to cache file',
				),
			),

			'Configuration Options'  => array(
				array(
					'arg'  => '--prepend <file>',
					'desc' => 'A PHP script that is included as early as possible',
				),
				array(
					'arg'  => '--bootstrap <file>',
					'desc' => 'A PHP script that is included before the tests run',
				),
				array(
					'arg'  => '-c|--configuration <file>',
					'desc' => 'Read configuration from XML file',
				),
				array(
					'arg'  => '--no-configuration',
					'desc' => 'Ignore default configuration file (phpunit.xml)',
				),
				array(
					'arg'  => '--extensions <extensions>',
					'desc' => 'A comma separated list of PHPUnit extensions to load',
				),
				array(
					'arg'  => '--no-extensions',
					'desc' => 'Do not load PHPUnit extensions',
				),
				array(
					'arg'  => '--include-path <path(s)>',
					'desc' => 'Prepend PHP\'s include_path with given path(s)',
				),
				array(
					'arg'  => '-d <key[=value]>',
					'desc' => 'Sets a php.ini value',
				),
				array(
					'arg'  => '--cache-result-file <file>',
					'desc' => 'Specify result cache path and filename',
				),
				array(
					'arg'  => '--generate-configuration',
					'desc' => 'Generate configuration file with suggested settings',
				),
				array(
					'arg'  => '--migrate-configuration',
					'desc' => 'Migrate configuration file to current format',
				),
			),
		);

		if ( defined( '__PHPUNIT_PHAR__' ) ) {
			$elements['PHAR Options'] = array(
				array(
					'arg'  => '--manifest',
					'desc' => 'Print Software Bill of Materials (SBOM) in plain-text format',
				),
				array(
					'arg'  => '--sbom',
					'desc' => 'Print Software Bill of Materials (SBOM) in CycloneDX XML format',
				),
				array(
					'arg'  => '--composer-lock',
					'desc' => 'Print composer.lock file used to build the PHAR',
				),
			);
		}

		$elements['Miscellaneous Options'] = array(
			array(
				'arg'  => '-h|--help',
				'desc' => 'Prints this usage information',
			),
			array(
				'arg'  => '--version',
				'desc' => 'Prints the version and exits',
			),
			array(
				'arg'  => '--atleast-version <min>',
				'desc' => 'Checks that version is greater than min and exits',
			),
			array(
				'arg'  => '--check-version',
				'desc' => 'Checks whether PHPUnit is the latest version and exits',
			),
		);

		return $elements;
	}
}
