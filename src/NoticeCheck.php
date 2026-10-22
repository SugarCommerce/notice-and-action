<?php
/**
 * Whether a report is complete enough to act on.
 *
 * @package   SugarCommerce\NoticeAndAction
 * @copyright Copyright (c) 2026, ArrayPress Limited
 * @license   GPL-2.0-or-later
 * @since     1.0.0
 */

declare( strict_types=1 );

namespace SugarCommerce\NoticeAndAction;

/**
 * Class NoticeCheck
 *
 * Anyone can report anything, and every report should reach a person. What
 * this decides is narrower: whether a report has what it needs to be acted
 * on. For copyright that is a legal question rather than a style one. A
 * notice without the work, the location, a way to answer, the good-faith
 * and accuracy statements and a signature is not a notice under 17 U.S.C.
 * § 512(c)(3), and a host that takes material down on an incomplete one is
 * acting without the protection the process gives it — and without the
 * counter-notice process the uploader is owed.
 *
 * Every report, of any kind, is held to the EU Digital Services Act's
 * Article 16(2) elements this can check mechanically: an explanation, an
 * exact location when one is given, and a name and address to answer.
 *
 * The fields, all already cleaned by the caller (this validates; it does
 * not sanitise):
 *
 * | Field        | Type   | Element |
 * |--------------|--------|---------|
 * | `category`   | string | What kind of problem. Required. |
 * | `name`       | string | Who is reporting. DSA 16(2)(c); DMCA (iv). |
 * | `email`      | string | Where to answer them; '' when not a valid address. DSA 16(2)(c); DMCA (iv). |
 * | `details`    | string | Why it is unlawful or against the rules. DSA 16(2)(a). |
 * | `location`   | string | The exact URL. DSA 16(2)(b); DMCA (iii). Optional except for copyright. |
 * | `work`       | string | The copyrighted work. DMCA (ii). |
 * | `good_faith` | int    | 1 when the good-faith statement is made. DMCA (v). |
 * | `accurate`   | int    | 1 when the accuracy statement, under penalty of perjury, is made. DMCA (vi); DSA 16(2)(d). |
 *
 * A statement counts as made only when its field is exactly 1, '1' or true.
 * One made under penalty of perjury has to be unambiguous, so a value that
 * merely casts to 1 — '1abc', 1.5, a non-empty array — is not one.
 * | `signature`  | string | A typed full name. DMCA (i). |
 *
 * Not legal advice. Which categories need the copyright elements, and what
 * happens to a report that lacks them — usually it still reaches a person,
 * and is answered — are the application's decisions.
 *
 * @since 1.0.0
 */
final readonly class NoticeCheck {

	/**
	 * The shortest explanation that can explain anything, in characters.
	 *
	 * @since 1.0.0
	 *
	 * @var int
	 */
	public const int EXPLANATION_MIN = 10;

	/**
	 * Every field the check reads.
	 *
	 * @since 1.0.0
	 *
	 * @var list<string>
	 */
	public const array FIELDS = array( 'category', 'name', 'email', 'details', 'location', 'work', 'good_faith', 'accurate', 'signature' );

	/**
	 * What is missing or wrong, by field.
	 *
	 * @since 1.0.0
	 *
	 * @param array<string, mixed> $fields    Cleaned fields; absent ones
	 *                                        count as empty.
	 * @param bool                 $copyright Whether the report is a
	 *                                        copyright notice, held to
	 *                                        § 512(c)(3) as well.
	 *
	 * @return array<string, Problem> Field => problem, in FIELDS order. Empty
	 *                                when the report can be acted on.
	 */
	public static function problems( array $fields, bool $copyright ): array {
		$fields   = $fields + array_fill_keys( self::FIELDS, '' );
		$location = self::text( $fields['location'] );

		$problems = array_filter( array(
			'category' => '' === self::text( $fields['category'] ) ? Problem::Missing : null,
			'name'     => '' === self::text( $fields['name'] ) ? Problem::Missing : null,
			'email'    => '' === self::text( $fields['email'] ) ? Problem::Missing : null,
			'details'  => mb_strlen( self::text( $fields['details'] ) ) < self::EXPLANATION_MIN ? Problem::TooShort : null,
			'location' => '' !== $location && ! self::is_url( $location ) ? Problem::NotAUrl : null,
		) );

		return $copyright ? $problems + self::copyright( $fields ) : $problems;
	}

	/**
	 * What a copyright notice is missing, on top of what every report needs.
	 *
	 * @since 1.0.0
	 *
	 * @param array<string, mixed> $fields Cleaned fields.
	 *
	 * @return array<string, Problem>
	 */
	public static function copyright( array $fields ): array {
		$fields = $fields + array_fill_keys( self::FIELDS, '' );

		return array_filter( array(
			'location'   => '' === self::text( $fields['location'] ) ? Problem::Missing : null,
			'work'       => '' === self::text( $fields['work'] ) ? Problem::Missing : null,
			'good_faith' => ! self::affirmed( $fields['good_faith'] ) ? Problem::Unaffirmed : null,
			'accurate'   => ! self::affirmed( $fields['accurate'] ) ? Problem::Unaffirmed : null,
			'signature'  => '' === self::text( $fields['signature'] ) ? Problem::Missing : null,
		) );
	}

	/**
	 * Whether a report has everything it needs.
	 *
	 * @since 1.0.0
	 *
	 * @param array<string, mixed> $fields    Cleaned fields.
	 * @param bool                 $copyright Whether it is a copyright notice.
	 *
	 * @return bool
	 */
	#[\NoDiscard( 'False means the report cannot be acted on as it is.' )]
	public static function complete( array $fields, bool $copyright ): bool {
		return array() === self::problems( $fields, $copyright );
	}

	/**
	 * Whether a statement field says, unambiguously, that it was made.
	 *
	 * @since 1.1.0
	 *
	 * @param mixed $value Field value.
	 *
	 * @return bool True only for 1, '1' or true.
	 */
	private static function affirmed( mixed $value ): bool {
		return 1 === $value || '1' === $value || true === $value;
	}

	/**
	 * A field as trimmed text.
	 *
	 * @since 1.0.0
	 *
	 * @param mixed $value Field value.
	 *
	 * @return string
	 */
	private static function text( mixed $value ): string {
		return is_scalar( $value ) ? trim( (string) $value ) : '';
	}

	/**
	 * Whether a location is an absolute http(s) URL.
	 *
	 * @since 1.0.0
	 *
	 * @param string $value Location.
	 *
	 * @return bool
	 */
	private static function is_url( string $value ): bool {
		return false !== filter_var( $value, FILTER_VALIDATE_URL ) && in_array( strtolower( (string) parse_url( $value, PHP_URL_SCHEME ) ), array( 'http', 'https' ), true );
	}
}
