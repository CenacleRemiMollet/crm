
> See https://keepachangelog.com/fr/1.0.0/

## [Unreleased]

### Added
- Login brute-force protection: `login_throttling` (5 attempts / 15 min), adds `symfony/rate-limiter` and `symfony/lock` (`LOCK_DSN=flock`)

### Changed
- Runtime target is now PHP 8.5 (`config.platform.php` pinned to 8.5.0)
- `composer.lock` refreshed (was from 2022): Symfony 5.4.x latest patches, twig 3.30, doctrine/orm 2.20, doctrine/dbal 3.10
- `phpoffice/phpspreadsheet` 1.23 → 5.10 (1.x does not support PHP 8.5)
- `nelmio/api-doc-bundle` pinned to `^4.11` instead of `dev-master`
- A user's login can only be changed by an admin
- Logs: daily rotated files (30 days), `deprecation` channel no longer logged (prod.log had reached 779 MB)

### Fixed
- PHP 8.4+ deprecations: implicit nullable parameters, optional parameter before required (`LocaleSubscriber`)
- Lesson update without `location_uuid` returned 404
- User update/creation crashed on `$this->manager` (undefined) and on undefined `$account` in club subscriptions
- XLSX export temporary file was never deleted
- `/api/search` always returned 500 (wrong `Pageable` import in `SearchController` and `SearchDao`)
- `db:dump` crashed with DBAL 3 (`Connection::getUsername()` removed)

### Security
- 72 known vulnerabilities fixed through dependency upgrades (twig, phpspreadsheet, symfony/http-kernel, security-http, http-foundation, yaml, mime...)
- Privilege escalation: a teacher/club manager could grant `ROLE_ADMIN`/`ROLE_SUPER_ADMIN` via `PATCH /api/users/{uuid}` (`roles`). Account roles are now admin-only, `ROLE_SUPER_ADMIN` super-admin-only
- Club subscription roles restricted to `ROLE_CLUB_MANAGER`/`ROLE_TEACHER`/`ROLE_STUDENT`, and never promoted to global roles in `Account::getRoles()`
- `ClubAccess`: write access now requires a manager/teacher role *in that club* (a teacher of club A who was a student of club B had write access to B)
- Lesson creation/update: location must belong to the same club
- CSV/XLSX user export: formula injection neutralized
- `db:dump` no longer prints the database password nor goes through a shell (`Process` + `MYSQL_PWD`)

Examples :

## [1.0.0] - 2019-08-05
### Added
- Functionatily "forgot password"

### Changed
- In the table user, add column 'truc'

### Fixed
- Crash with Spring Boot 2.1.0 (`mbeanExporter` already defined) 
