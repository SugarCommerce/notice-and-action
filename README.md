# Notice and Action

The structure a hosting service's notice-and-action process needs: the elements a report or a copyright notice must have before it can be acted on (EU Digital Services Act Article 16, US DMCA § 512(c)(3)), the statement of reasons a restriction must come with (DSA Article 17), and public references for reports. Returns codes, never wording, so every sentence stays yours to translate. Zero dependencies.

## Why

Anything that hosts other people's files gets reports, and the law has opinions about both ends of the process:

- **Acting on an incomplete copyright notice.** A notice without the work, the location, a way to answer, the good-faith and accuracy statements and a signature is not a notice under § 512(c)(3). Taking material down on one is acting without the safe harbour — and without the counter-notice process the uploader is owed.
- **A takedown email that says only what happened.** Article 17 wants the restriction, the facts relied on, the ground, whether a machine detected or decided, and how to contest it. "Your file was removed" is none of those but the first.
- **Two questions answered as one.** "Was this automated?" is two questions in Article 17(3)(c): detection and decision. A scanner that flags a file for a person and a scanner that removes it are different answers.

The checks are mechanical, and easy to get subtly wrong in every app that writes them again. The wording is legal and local, so it stays in the app.

## Features

- ✅ **`NoticeCheck`** — names what a report or a copyright notice lacks, field by field, as `Problem` codes.
- 📜 **`StatementOfReasons`** — refuses a statement missing the restriction, the facts, the ground or the redress.
- 🤖 **`Automation`** — detection and decision as separate answers.
- 🔖 **`Reference`** — `R-000042`, and back again from whatever was pasted.
- 🌍 **No wording** — codes only, so the form speaks the reporter's language.

## Requirements

PHP 8.5+

## Installation

```bash
composer require sugarcommerce/notice-and-action
```

## Checking a report

Clean the input first (this validates; it does not sanitise), then ask what is missing:

```php
use SugarCommerce\NoticeAndAction\{NoticeCheck, Problem};

$problems = NoticeCheck::problems( $fields, copyright: 'copyright' === $fields['category'] );

foreach ( $problems as $field => $problem ) {
    $errors[ $field ] = match ( [ $field, $problem ] ) {
        [ 'location', Problem::NotAUrl ] => __( 'Give the address of the page, starting https://' ),
        [ 'location', Problem::Missing ] => __( 'Give the address where the infringing material is.' ),
        [ 'accurate', Problem::Unaffirmed ] => __( 'A notice needs the statement that it is accurate, under penalty of perjury.' ),
        // …
    };
}
```

| Field | Every report | Copyright notice | Source |
|---|---|---|---|
| `category` | required | required | — |
| `name` | required | required | DSA 16(2)(c); DMCA (iv) |
| `email` | required (pass `''` when invalid) | required | DSA 16(2)(c); DMCA (iv) |
| `details` | at least 10 characters | at least 10 characters | DSA 16(2)(a) |
| `location` | an http(s) URL if given | required | DSA 16(2)(b); DMCA (iii) |
| `work` | — | required | DMCA (ii) |
| `good_faith` | — | `1` | DMCA (v) |
| `accurate` | — | `1` | DMCA (vi); DSA 16(2)(d) |
| `signature` | — | required | DMCA (i) |

A statement is made only by exactly `1`, `'1'` or `true`. One made under penalty of perjury has to be unambiguous, so `'1abc'`, `1.5` or a checkbox's `'on'` is not one — map your form's value to `1` when you clean it.

Which categories count as copyright, and what happens to a report that is incomplete — usually it still reaches a person — are yours to decide.

## Statements of reasons

```php
use SugarCommerce\NoticeAndAction\{Automation, StatementOfReasons};

$statement = StatementOfReasons::make(
    restriction: 'File removed',
    subject:     '"album.zip"',
    facts:       'Our malware scanner found Eicar-Test-Signature in this file.',
    ground:      'Malware: acceptable use policy, harmful files.',
    redress:     'Reply to this email to appeal. A person reviews every appeal.',
    automation:  Automation::Both,
);

$statement->to_array();   // restriction, subject, facts, ground, automated_detection, automated_decision, redress, date
```

`make()` throws `InvalidArgumentException` naming the missing elements, so a notice cannot go out without them.

## References

```php
use SugarCommerce\NoticeAndAction\Reference;

Reference::format( 42 );            // R-000042
Reference::parse( ' r-000042 ' );   // 42
```

## Not legal advice

This encodes the elements the texts list, as of 2026. It does not replace reading them, and it does not produce the Article 24(5) submission to the Commission's transparency database, which has its own schema.

## Testing

```bash
composer install
composer test
```

26 tests: every element of a report and of a copyright notice, statements that only cast to 1, URL schemes, character counting, field order, every refusal of an incomplete statement of reasons, the four automation answers, and reference round trips.

## License

GPL-2.0-or-later
