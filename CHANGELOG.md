# Changelog

## 1.0.0 - 2026-04-13
- First stable public release of `SkritPHP`.
- Includes full PHP port of Skrit core modules and unified router:
  - `satrovacki`
  - `utrovacki`
  - `leetrovacki`
  - `auto` mode detection and decode/encode routing
- Includes Laravel integration:
  - service provider
  - facade
  - publishable config
- Includes Livewire usage example.
- Includes full community standards and templates (`CODE_OF_CONDUCT`, `CONTRIBUTING`, `SECURITY`, `SUPPORT`, issue/PR templates).
- CI/automation included (`CI workflow`, `Dependabot`).
- Test suite and strict coverage gate enforced at 100%.
- Merged PR #1: CI checkout action bumped to `actions/checkout@v6`.

## 0.1.1 - 2026-04-13
- Added full community standards and governance docs:
  - `CODE_OF_CONDUCT.md`
  - `CONTRIBUTING.md`
  - `SECURITY.md`
  - `SUPPORT.md`
  - `LICENSE` (GPL-3.0-or-later)
- Added GitHub community templates:
  - issue templates (`bug`, `feature`, `question`)
  - pull request template
- Added GitHub automation:
  - CI workflow (`.github/workflows/ci.yml`)
  - Dependabot config (`.github/dependabot.yml`)
- Added strict coverage tooling and gate scripts (`composer test:strict`) with enforced 100% coverage checks.
- Updated README with GitHub/Packagist-style badges and community sections.

## 0.1.0 - 2026-04-13
- Initial PHP port of Skrit Python module (`satrovacki`, `utrovacki`, `leetrovacki`, unified auto router).
- Added Cyrillic/Latin transliteration support and option parity (`plain_c_target`, `soft_tj_to_cyrillic`).
- Added leet profiles (`basic`, `readable`, `full`), complexity and density support.
- Added Laravel integration:
  - `SkritServiceProvider`
  - `Skrit` facade
  - config publishing (`skrit-config`)
- Added Livewire example component and blade view.
- Added PHPUnit parity/unit/integration tests.
