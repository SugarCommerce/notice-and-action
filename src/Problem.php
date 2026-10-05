<?php
/**
 * What is wrong with one field of a notice.
 *
 * @package   SugarCommerce\NoticeAndAction
 * @copyright Copyright (c) 2026, ArrayPress Limited
 * @license   GPL-2.0-or-later
 * @since     1.0.0
 */

declare( strict_types=1 );

namespace SugarCommerce\NoticeAndAction;

/**
 * Enum Problem
 *
 * A code rather than a sentence: the person filling in the form reads the
 * explanation in their own language, and the wording of a legal form is the
 * application's to choose, not a library's.
 *
 * @since 1.0.0
 */
enum Problem: string {

	/** Required and not given. */
	case Missing = 'missing';

	/** Given, but too short to explain anything. */
	case TooShort = 'too_short';

	/** Meant to be a URL, and is not one. */
	case NotAUrl = 'not_a_url';

	/** A statement the notice must make, not made. */
	case Unaffirmed = 'unaffirmed';
}
