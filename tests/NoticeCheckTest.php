<?php
/**
 * NoticeCheck test suite.
 *
 * @package   SugarCommerce\NoticeAndAction
 * @copyright Copyright (c) 2026, ArrayPress Limited
 * @license   GPL-2.0-or-later
 * @since     1.0.0
 */

declare( strict_types=1 );

namespace SugarCommerce\NoticeAndAction\Tests;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use SugarCommerce\NoticeAndAction\{NoticeCheck, Problem};

/**
 * Class NoticeCheckTest
 *
 * What a report needs before it can be acted on, and what a copyright
 * notice needs on top.
 */
final class NoticeCheckTest extends TestCase {

	private static function report( array $overrides = array() ): array {
		return $overrides + array(
			'category' => 'scam',
			'name'     => 'Ada Reporter',
			'email'    => 'ada@example.com',
			'details'  => 'This shop sells a course that does not exist.',
		);
	}

	private static function notice( array $overrides = array() ): array {
		return $overrides + self::report( array(
			'category'   => 'copyright',
			'location'   => 'https://shop.example/s/midnight',
			'work'       => 'Our album "Night Bus", released 2024.',
			'good_faith' => 1,
			'accurate'   => 1,
			'signature'  => 'Ada Reporter',
		) );
	}

	/* ─── Every report ─────────────────────────────────────────────────── */

	public function test_a_complete_report_has_no_problems(): void {
		$this->assertSame( array(), NoticeCheck::problems( self::report(), false ) );
		$this->assertTrue( NoticeCheck::complete( self::report(), false ) );
	}

	/**
	 * @return array<string, array{string, mixed, Problem}>
	 */
	public static function broken(): array {
		return array(
			'no category'       => array( 'category', '', Problem::Missing ),
			'no name'           => array( 'name', '  ', Problem::Missing ),
			'no email'          => array( 'email', '', Problem::Missing ),
			'a word of details' => array( 'details', 'bad', Problem::TooShort ),
			'not a url'         => array( 'location', 'my shop', Problem::NotAUrl ),
			'not a web url'     => array( 'location', 'javascript:alert(1)', Problem::NotAUrl ),
			'an array'          => array( 'name', array( 'x' ), Problem::Missing ),
		);
	}

	#[DataProvider( 'broken' )]
	public function test_each_missing_element_is_named( string $field, mixed $value, Problem $problem ): void {
		$this->assertSame( array( $field => $problem ), NoticeCheck::problems( self::report( array( $field => $value ) ), false ) );
	}

	public function test_a_location_is_optional_outside_copyright(): void {
		$this->assertArrayNotHasKey( 'location', NoticeCheck::problems( self::report(), false ) );
	}

	public function test_details_are_counted_in_characters_not_bytes(): void {
		$this->assertSame( array(), NoticeCheck::problems( self::report( array( 'details' => 'éééééééééé' ) ), false ) );
	}

	/* ─── Copyright ────────────────────────────────────────────────────── */

	public function test_a_complete_notice_has_no_problems(): void {
		$this->assertSame( array(), NoticeCheck::problems( self::notice(), true ) );
	}

	public function test_a_notice_names_every_element_it_lacks(): void {
		$problems = NoticeCheck::problems( self::report( array( 'category' => 'copyright' ) ), true );

		$this->assertSame(
			array(
				'location'   => Problem::Missing,
				'work'       => Problem::Missing,
				'good_faith' => Problem::Unaffirmed,
				'accurate'   => Problem::Unaffirmed,
				'signature'  => Problem::Missing,
			),
			$problems
		);
	}

	public function test_a_bad_url_is_reported_once_as_a_bad_url(): void {
		$this->assertSame( array( 'location' => Problem::NotAUrl ), NoticeCheck::problems( self::notice( array( 'location' => 'here' ) ), true ) );
	}

	public function test_statements_must_be_exactly_affirmed(): void {
		$this->assertSame( array( 'good_faith' => Problem::Unaffirmed ), NoticeCheck::copyright( self::notice( array( 'good_faith' => 0 ) ) ) );
		$this->assertSame( array(), NoticeCheck::copyright( self::notice( array( 'accurate' => '1' ) ) ) );
	}

	public function test_problems_come_in_field_order(): void {
		$problems = NoticeCheck::problems( array( 'details' => '', 'category' => '' ), true );

		$this->assertSame( array( 'category', 'name', 'email', 'details', 'location', 'work', 'good_faith', 'accurate', 'signature' ), array_keys( $problems ) );
	}
}
