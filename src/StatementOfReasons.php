<?php
/**
 * What a hosting service must tell somebody whose content it restricted.
 *
 * @package   SugarCommerce\NoticeAndAction
 * @copyright Copyright (c) 2026, ArrayPress Limited
 * @license   GPL-2.0-or-later
 * @since     1.0.0
 */

declare( strict_types=1 );

namespace SugarCommerce\NoticeAndAction;

/**
 * Class StatementOfReasons
 *
 * EU Digital Services Act Article 17: when a hosting service removes or
 * disables content, suspends an account or restricts how it can be paid,
 * it owes the person a clear and specific statement of reasons. The usual
 * failure is a notice that says *what* happened and nothing else — "your
 * file was removed" — when the statement has to give, at least:
 *
 * - (a) **the restriction** — what was done, and its scope;
 * - (b) **the facts and circumstances** relied on;
 * - (c) **whether automated means** were used, to detect and to decide
 *   (see Automation);
 * - (d)/(e) **the ground** — the law, or the terms-of-service clause, it
 *   breached;
 * - (f) **the redress** available — how to contest it.
 *
 * make() refuses a statement missing (a), (b), (d)/(e) or (f), so a
 * notice cannot be queued without them. The wording of each is the
 * application's; this holds the shape and the checking.
 *
 * Not legal advice, and not the Article 24(5) submission to the
 * Commission's transparency database, which has its own schema.
 *
 * @since 1.0.0
 */
final readonly class StatementOfReasons {

	/**
	 * The elements make() requires, in Article 17(3) order.
	 *
	 * @since 1.0.0
	 *
	 * @var list<string>
	 */
	public const array REQUIRED = array( 'restriction', 'facts', 'ground', 'redress' );

	/**
	 * Hold a statement; make() is the way in, and checks it.
	 *
	 * @since 1.0.0
	 *
	 * @param string     $restriction What was done: "File removed", "Account suspended".
	 * @param string     $subject     What it was done to: a file's name, an account.
	 * @param string     $facts       Why: the facts and circumstances relied on.
	 * @param string     $ground      The law or policy clause breached.
	 * @param Automation $automation  Whether a machine detected or decided.
	 * @param string     $redress     How to contest it.
	 * @param int        $date        When it took effect, Unix time.
	 */
	private function __construct(
		public string $restriction,
		public string $subject,
		public string $facts,
		public string $ground,
		public Automation $automation,
		public string $redress,
		public int $date,
	) {}

	/**
	 * A statement, refused when an element Article 17 requires is missing.
	 *
	 * @since 1.0.0
	 *
	 * @param string     $restriction What was done.
	 * @param string     $subject     What it was done to. May be '' where the
	 *                                restriction names it (an account).
	 * @param string     $facts       The facts and circumstances.
	 * @param string     $ground      The law or policy clause.
	 * @param string     $redress     How to contest it.
	 * @param Automation $automation  Whether a machine detected or decided.
	 * @param int|null   $date        When it took effect. Null is now.
	 *
	 * @return self
	 *
	 * @throws \InvalidArgumentException Naming the missing elements.
	 */
	public static function make( string $restriction, string $subject, string $facts, string $ground, string $redress, Automation $automation = Automation::None, ?int $date = null ): self {
		$statement = new self( trim( $restriction ), trim( $subject ), trim( $facts ), trim( $ground ), $automation, trim( $redress ), $date ?? time() );
		$missing   = $statement->missing();

		if ( array() !== $missing ) {
			throw new \InvalidArgumentException( 'A statement of reasons needs: ' . implode( ', ', $missing ) . '.' );
		}

		return $statement;
	}

	/**
	 * Which required elements are empty.
	 *
	 * @since 1.0.0
	 *
	 * @return list<string> From REQUIRED.
	 */
	public function missing(): array {
		return array_values( array_filter( self::REQUIRED, fn ( string $element ): bool => '' === $this->{$element} ) );
	}

	/**
	 * The statement as plain data, for a template or a record.
	 *
	 * @since 1.0.0
	 *
	 * @return array{restriction: string, subject: string, facts: string, ground: string, automated_detection: bool, automated_decision: bool, redress: string, date: int}
	 */
	public function to_array(): array {
		return array(
			'restriction'         => $this->restriction,
			'subject'             => $this->subject,
			'facts'               => $this->facts,
			'ground'              => $this->ground,
			'automated_detection' => $this->automation->detected(),
			'automated_decision'  => $this->automation->decided(),
			'redress'             => $this->redress,
			'date'                => $this->date,
		);
	}
}
