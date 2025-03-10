<?php declare(strict_types=1);

namespace PhpParser\Node\Expr;

use PhpParser\Node\Expr;

class Yield_ extends Expr {
	/** @var null|Expr Key expression */
	public ?Expr $key;
	/** @var null|Expr Value expression */
	public ?Expr $value;

	/**
	 * Constructs a yield expression node.
	 *
	 * @param null|Expr            $value Value expression
	 * @param null|Expr            $key Key expression
	 * @param array<string, mixed> $attributes Additional attributes
	 */
	public function __construct( ?Expr $value = null, ?Expr $key = null, array $attributes = array() ) {
		$this->attributes = $attributes;
		$this->key        = $key;
		$this->value      = $value;
	}

	public function getSubNodeNames(): array {
		return array( 'key', 'value' );
	}

	public function getType(): string {
		return 'Expr_Yield';
	}
}
