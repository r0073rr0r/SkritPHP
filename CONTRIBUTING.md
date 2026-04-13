# Contributing to SkritPHP

Thanks for contributing.

## Development Setup

1. Use PHP 8.2+.
2. Clone the repository.
3. Install dependencies:

```bash
composer install
```

4. Run tests:

```bash
composer test
```

5. Run strict coverage gate (required):

```bash
composer test:strict
```

## Project Scope

SkritPHP contains multiple text transformation modules:

- `src/Satrovacki.php`
- `src/Utrovacki.php`
- `src/Leet.php`
- `src/Leetrovacki.php`
- `src/Skrit.php` (main router API)

If you add a new transformation mode, wire it through `src/Skrit.php` and add
tests.

## Coding Guidelines

- Keep code and comments in English.
- Preserve existing behavior unless your PR explicitly changes a rule.
- Add or update tests for every behavior change.
- Keep examples in `README.md` aligned with real output.
- Keep coverage at 100%.

## Pull Requests

1. Open an issue first for non-trivial changes.
2. Create a focused branch and keep the PR small.
3. Include:
   - what changed
   - why it changed
   - test and coverage details
4. Ensure all tests pass before requesting review.

## Commit Messages

Use clear, scoped commit messages. Example:

`feat(leet): add readable profile edge-case handling`
