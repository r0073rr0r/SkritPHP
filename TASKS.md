# SkritPHP Tasks (Port from Python `skrit` v0.5.0)

## Cilj
Napraviti PHP paket koji:
1. reprodukuje `skrit` Python ponašanje (`satro`, `utro`, `leet`, `auto` detect, encode/decode),
2. može da se instalira preko Composer-a,
3. radi u Laravel aplikaciji (uključujući Livewire komponente),
4. podržava ista ključna podešavanja kao Python verzija.

## Izvor istine
- Python modul: `D:\Projects\Skrit`
- Kontekst projekta: `D:\Projects\Skrit\secrets\agents.md`
- Referenca ponašanja: Python testovi u `D:\Projects\Skrit\tests`

## Strategija (kako ću da radim)
1. Prvo portujem core algoritme 1:1 (bez Laravel zavisnosti), da dobijemo identične izlaze.
2. Zatim gradim Laravel adapter (ServiceProvider, config, Facade, optional Artisan command).
3. Na kraju dodajem Livewire primer i E2E verifikaciju preko fixture testova.
4. Svaki korak zaključujem testovima pre prelaska na sledeći.

## Task Backlog

### 1) Bootstrap PHP paketa
- [x] Inicijalizovati Composer paket (`r0073rr0r/skritphp`).
- [x] Podesiti PSR-4 namespace (predlog: `Skrit\\`).
- [x] Dodati osnovnu strukturu:
  - `src/`
  - `tests/`
  - `config/skrit.php`
  - `README.md`
- [x] Definisati PHP podržanu verziju (predlog: `^8.2`).

Acceptance:
- Paket se instalira lokalno kroz Composer bez greške.

### 2) Shared Core Utilities
- [x] Portovati transliteraciju Latinica <-> Ćirilica:
  - digrafi: `lj`, `nj`, `dž`
  - opcije: `plain_c_target` (`ц|ч|ћ`), `soft_tj_to_cyrillic`
- [x] Portovati tokenizer ekvivalent Python `WORD_OR_OTHER_PATTERN`.
- [x] Implementirati case-preserving helper (`UPPER`, `Title`, `lower`, mixed fallback).

Acceptance:
- Unit testovi pokrivaju sve transliteration i tokenization edge-case primere iz Python testova.

### 3) `Satrovacki` port
- [x] Implementirati:
  - `encode(string $text): string`
  - `encodeWord(string $word): string`
  - `decode(string $text): string`
  - `decodeWord(string $word): string`
  - `canDecodeWord(string $word): bool`
- [x] Preslikati heuristike:
  - split po prvom vokalskom bloku,
  - fallback `len/2` kada nema vokala,
  - slogotvorno `r` pravilo,
  - exceptions mapa.

Acceptance:
- Svi relevantni slučajevi iz `test_satrovacki.py` prolaze u PHP testovima.

### 4) `Utrovacki` port
- [x] Portovati format: `prefix + second + infix + first + suffix`.
- [x] Podržati custom `prefix/infix/suffix`.
- [x] Portovati decode parser (`_split_encoded_parts` ekvivalent) i `canDecodeWord`.

Acceptance:
- Svi slučajevi iz `test_utrovacki.py` prolaze 1:1.

### 5) `Leet` core port
- [x] Portovati `LEET_TABLE`, profile (`basic`, `readable`, `full`), complexity i density logiku.
- [x] Implementirati:
  - `availableProfiles()`
  - `getLeetProfile()`
  - `applyLeet()`
  - `looksLikeLeet()`
  - `LeetEncoder`

Acceptance:
- Svi slučajevi iz `test_leet.py` prolaze.

### 6) `Leetrovacki` port
- [x] Portovati `base_mode` (`auto|utro|satro`), `za_style`, `nje_style`, `prefix_style`.
- [x] Preslikati ponašanje sa punim leet profilom i utro-special replacement granama.
- [x] Validacija ulaznih opcija identična Python ponašanju.

Acceptance:
- Svi slučajevi iz `test_leetrovacki.py` prolaze.

### 7) Unified router (`Skrit`) port
- [x] Implementirati:
  - `detectMode()`
  - `detectLeetBase()`
  - `encodeText()` sa auto encode/decode granama.
- [x] Portovati basic deleet helper i auto decode heuristike.

Acceptance:
- Svi slučajevi iz `test_skrit.py` i `test_skrit_cli_coverage.py` prolaze (u PHP adaptaciji).

### 8) Laravel integracija
- [x] Dodati `SkritServiceProvider` (auto-discovery).
- [x] Dodati config publish (`skrit.php`) sa svim relevantnim default opcijama.
- [x] Dodati Facade (`Skrit`) i bind interfejsa u container.
- [x] Dodati jednostavan API servis:
  - `encodeText(...)`
  - `decodeText(...)` (router u `auto` režimu)

Acceptance:
- U Laravel projektu radi:
  - `composer require ...`
  - `php artisan vendor:publish --tag=skrit-config`
  - `Skrit::encodeText(...)`

### 9) Livewire upotreba
- [x] Dodati primer Livewire komponente sa state-om:
  - input poruka
  - mode (`auto/satro/utro/leet`)
  - opcije (`plain_c_target`, `soft_tj`, `leet_profile`, itd.)
  - output (encoded/decoded + detected mode)
- [x] Dodati primer Blade view-a za interaktivni prikaz.

Acceptance:
- Primer komponenta radi bez dodatnog custom koda van dokumentovanog setup-a.

### 10) Kompatibilnost testova (Python vs PHP)
- [x] Napraviti fixture skup ulaza/izlaza iz Python reference.
- [x] Dodati parity testove koji proveravaju da PHP daje isti rezultat.
- [x] Pokriti latinicu, ćirilicu i mixed script scenarije.

Acceptance:
- Parity testovi prolaze za dogovoreni fixture set.

### 11) Dokumentacija i release
- [x] README:
  - install
  - pure PHP usage
  - Laravel usage
  - Livewire usage
  - opcije i primeri
- [x] CHANGELOG + semver početni release (`0.1.0`).
- [x] Priprema za Packagist objavu.

Acceptance:
- Paket je spreman za javnu ili privatnu distribuciju.

## Definicija gotovog (MVP)
- Core API radi i testiran je.
- Laravel integracija radi kroz ServiceProvider + Facade.
- Livewire primer potvrđen.
- Rezultati za ključne primere su kompatibilni sa Python referencom.

