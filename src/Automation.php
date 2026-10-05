<?php
/**
 * How much of a decision a machine made.
 *
 * @package   SugarCommerce\NoticeAndAction
 * @copyright Copyright (c) 2026, ArrayPress Limited
 * @license   GPL-2.0-or-later
 * @since     1.0.0
 */

declare( strict_types=1 );

namespace SugarCommerce\NoticeAndAction;

/**
 * Enum Automation
 *
 * DSA Article 17(3)(c) asks two separate questions — was the content
 * detected by automated means, and was the decision taken by them — so a
 * yes/no flag is not enough. A malware scanner that blocks a file did both;
 * a scanner that flags a file for a person to block did only the first; a
 * rule that suspends an account after three strikes decided without
 * detecting anything itself.
 *
 * @since 1.0.0
 */
enum Automation: string {

	/** A person found it and a person decided. */
	case None = 'none';

	/** Found by automated means; a person decided. */
	case Detected = 'detected';

	/** A person or a report found it; a rule decided. */
	case Decided = 'decided';

	/** Found and decided by automated means. */
	case Both = 'both';

	/**
	 * Whether automated means detected the content.
	 *
	 * @since 1.0.0
	 *
	 * @return bool
	 */
	public function detected(): bool {
		return self::Detected === $this || self::Both === $this;
	}

	/**
	 * Whether automated means took the decision.
	 *
	 * @since 1.0.0
	 *
	 * @return bool
	 */
	public function decided(): bool {
		return self::Decided === $this || self::Both === $this;
	}
}
