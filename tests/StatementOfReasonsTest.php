<?php
/**
 * StatementOfReasons, Automation and Reference test suite.
 *
 * @package   SugarCommerce\NoticeAndAction
 * @copyright Copyright (c) 2026, ArrayPress Limited
 * @license   GPL-2.0-or-later
 * @since     1.0.0
 */

declare( strict_types=1 );

namespace SugarCommerce\NoticeAndAction\Tests;

use PHPUnit\Framework\TestCase;
use SugarCommerce\NoticeAndAction\{Automation, NoticeCheck, Reference, StatementOfReasons};

/**
 * Class StatementOfReasonsTest
 *
 * A restriction cannot be announced without the elements Article 17 asks
 * for.
 */
final class StatementOfReasonsTest extends TestCase {

	/* ─── Statement of reasons ─────────────────────────────────────────── */

	public function test_a_statement_holds_every_element(): void {
		$statement = StatementOfReasons::make( 'File removed', '"album.zip"', 'Our scanner found Eicar.', 'Malware: acceptable use policy, harmful files.', 'Reply to appeal.', Automation::Both, 1_800_000_000 );

		$this->assertSame(
			array(
				'restriction'         => 'File removed',
				'subject'             => '"album.zip"',
				'facts'               => 'Our scanner found Eicar.',
				'ground'              => 'Malware: acceptable use policy, harmful files.',
				'automated_detection' => true,
				'automated_decision'  => true,
				'redress'             => 'Reply to appeal.',
				'date'                => 1_800_000_000,
			),
			$statement->to_array()
		);
		$this->assertSame( array(), $statement->missing() );
	}

	public function test_a_statement_without_its_reasons_is_refused(): void {
		$this->expectException( \InvalidArgumentException::class );
		$this->expectExceptionMessage( 'facts, ground' );

		StatementOfReasons::make( 'File removed', 'x', ' ', '', 'Reply to appeal.' );
	}

	public function test_a_statement_without_redress_is_refused(): void {
		$this->expectException( \InvalidArgumentException::class );

		StatementOfReasons::make( 'Account suspended', '', 'Three strikes.', 'Repeat infringement policy.', '' );
	}

	public function test_the_subject_may_be_left_to_the_restriction(): void {
		$this->assertSame( '', StatementOfReasons::make( 'Account suspended', '', 'Three strikes.', 'Repeat infringement.', 'Reply.' )->subject );
	}

	public function test_the_date_defaults_to_now(): void {
		$this->assertEqualsWithDelta( time(), StatementOfReasons::make( 'r', '', 'f', 'g', 'x' )->date, 5 );
	}

	/* ─── Automation ───────────────────────────────────────────────────── */

	public function test_detection_and_decision_are_separate_questions(): void {
		$this->assertSame( array( false, false ), array( Automation::None->detected(), Automation::None->decided() ) );
		$this->assertSame( array( true, false ), array( Automation::Detected->detected(), Automation::Detected->decided() ) );
		$this->assertSame( array( false, true ), array( Automation::Decided->detected(), Automation::Decided->decided() ) );
		$this->assertSame( array( true, true ), array( Automation::Both->detected(), Automation::Both->decided() ) );
	}

	/* ─── Reference ────────────────────────────────────────────────────── */

	public function test_a_reference_is_fixed_width(): void {
		$this->assertSame( 'R-000042', Reference::format( 42 ) );
		$this->assertSame( 'R-1234567', Reference::format( 1234567 ) );
		$this->assertSame( 'T-0042', Reference::format( 42, 'T-', 4 ) );
	}

	public function test_a_pasted_reference_is_read_back(): void {
		$this->assertSame( 42, Reference::parse( ' r-000042 ' ) );
		$this->assertSame( 42, Reference::parse( Reference::format( 42 ) ) );
		$this->assertNull( Reference::parse( 'R-' ) );
		$this->assertNull( Reference::parse( 'R-00x1' ) );
		$this->assertNull( Reference::parse( 'Q-000042' ) );
		$this->assertNull( Reference::parse( 'R-000000' ) );
	}

	/* ─── Must check ───────────────────────────────────────────────────── */

	public function test_completeness_must_be_used(): void {
		$attributes = \ReflectionMethod::createFromMethodName( NoticeCheck::class . '::complete' )->getAttributes( \NoDiscard::class );

		$this->assertCount( 1, $attributes );
	}
}
