<?php declare(strict_types = 1);
/*
 * This file is part of PharIo\Version.
 *
 * (c) Arne Blankerts <arne@blankerts.de>, Sebastian Heuer <sebastian@phpeople.de>, Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PharIo\Version;

class AndVersionConstraintGroup extends AbstractVersionConstraint {
	/** @var VersionConstraint[] */
	private $constraints = array();

	/**
	 * @param VersionConstraint[] $constraints
	 */
	public function __construct( string $originalValue, array $constraints ) {
		parent::__construct( $originalValue );

		$this->constraints = $constraints;
	}

	public function complies( Version $version ): bool {
		foreach ( $this->constraints as $constraint ) {
			if ( ! $constraint->complies( $version ) ) {
				return false;
			}
		}

		return true;
	}
}
