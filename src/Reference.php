<?php
/**
 * A report's public reference.
 *
 * @package   SugarCommerce\NoticeAndAction
 * @copyright Copyright (c) 2026, ArrayPress Limited
 * @license   GPL-2.0-or-later
 * @since     1.0.0
 */

declare( strict_types=1 );

namespace SugarCommerce\NoticeAndAction;

/**
 * Class Reference
 *
 * What a reporter quotes back — "R-000042" — so a reply, an appeal or a
 * follow-up email finds the report it is about. Fixed width so references
 * sort and read alike, and parseable so a pasted one can be looked up
 * whatever case or spacing it arrives in.
 *
 * @since 1.0.0
 */
final readonly class Reference {

	/**
	 * The default prefix.
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	public const string PREFIX = 'R-';

	/**
	 * The default number of digits.
	 *
	 * @since 1.0.0
	 *
	 * @var int
	 */
	public const int DIGITS = 6;

	/**
	 * A report id as a reference.
	 *
	 * @since 1.0.0
	 *
	 * @param int    $id     Report id.
	 * @param string $prefix What it starts with.
	 * @param int    $digits Zero-padded to at least this many.
	 *
	 * @return string e.g. R-000042.
	 */
	public static function format( int $id, string $prefix = self::PREFIX, int $digits = self::DIGITS ): string {
		return $prefix . str_pad( (string) max( 0, $id ), max( 1, $digits ), '0', STR_PAD_LEFT );
	}

	/**
	 * The report id in a reference, or null when it is not one.
	 *
	 * @since 1.0.0
	 *
	 * @param string $reference As pasted: " r-000042 ".
	 * @param string $prefix    What it starts with.
	 *
	 * @return int|null Null too for a number too big to be an id, rather
	 *                  than PHP_INT_MAX, which a cast would quietly give.
	 */
	public static function parse( string $reference, string $prefix = self::PREFIX ): ?int {
		$reference = strtoupper( trim( $reference ) );
		$prefix    = strtoupper( $prefix );

		if ( ! str_starts_with( $reference, $prefix ) ) {
			return null;
		}

		$digits = substr( $reference, strlen( $prefix ) );

		if ( '' === $digits || ! ctype_digit( $digits ) ) {
			return null;
		}

		$id = filter_var( ltrim( $digits, '0' ), FILTER_VALIDATE_INT, array( 'options' => array( 'min_range' => 1 ) ) );

		return false === $id ? null : $id;
	}
}
