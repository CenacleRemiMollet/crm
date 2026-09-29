
> See https://keepachangelog.com/fr/1.0.0/

## [Unreleased]

### Added
- Planning colors (club "Horaires & tarifs" page) configurable per discipline in the admin (`/config`), stored as `planning.color.<discipline>` properties; CSS defaults kept when nothing is configured, `default` value resets a color
- Planning color per lesson (lesson edit/creation form, color swatch in the club lessons list); empty = color of the discipline (admin `/config`, else CSS default). **Schema change**, to run once per database: `php bin/console dbal:run-sql "ALTER TABLE club_lesson ADD COLUMN IF NOT EXISTS color VARCHAR(7) DEFAULT NULL"`
- Login brute-force protection: `login_throttling` (5 attempts / 15 min), adds `symfony/rate-limiter` and `symfony/lock` (`LOCK_DSN=flock`)

### Changed
- Runtime target is now PHP 8.5 (`config.platform.php` pinned to 8.5.0)
- `composer.lock` refreshed (was from 2022): Symfony 5.4.x latest patches, twig 3.30, doctrine/orm 2.20, doctrine/dbal 3.10
- `phpoffice/phpspreadsheet` 1.23 → 5.10 (1.x does not support PHP 8.5)
- `nelmio/api-doc-bundle` pinned to `^4.11` instead of `dev-master`
- A user's login can only be changed by an admin
- Logs: daily rotated files (30 days), `deprecation` channel no longer logged (prod.log had reached 779 MB)
- Symfony 5.4 → 7.4 (LTS), PHP requirement `>=8.4` (DoctrineBundle 3 requires native lazy objects)
- Doctrine ORM 2.20 → 3.7, DBAL 3.10 → 4.5, DoctrineBundle 2.13 → 3.3, persistence 2 → 4, migrations-bundle 3 → 4, fixtures-bundle 3 → 4
- `nelmio/api-doc-bundle` 4 → 5.12, `zircote/swagger-php` 4 → 6.11, `symfony/monolog-bundle` 3 → 4.1 (monolog 3), `symfony/flex` 1 → 2
- All docblock annotations replaced by PHP attributes: routing, Doctrine mapping (`type: attribute`), validation (incl. `App\Validator\Constraints\*`), `#[IsGranted]` (Symfony's), JMS serializer, Hateoas, OpenAPI (`OpenApi\Attributes`)
- `doctrine/dbal`, `jms/serializer`, `symfony/lock` now required explicitly (used directly, were transitive)
- `default_table_options` pinned to `utf8mb4` / `utf8mb4_unicode_ci` (DBAL 4 no longer forces it). On existing databases `doctrine:schema:update` only proposes dropping the legacy `(DC2Type:json)` column comments (harmless)
- Access denied messages are more explicit (`Access Denied. The user doesn't have ROLE_ADMIN.`)

### Removed
- `sensio/framework-extra-bundle`, `doctrine/annotations`, `symfony/proxy-manager-bridge`, `composer/package-versions-deprecated` (abandoned / unsupported by Symfony 7)

### Fixed
- PHP 8.4+ deprecations: implicit nullable parameters, optional parameter before required (`LocaleSubscriber`)
- Lesson update without `location_uuid` returned 404
- User update/creation crashed on `$this->manager` (undefined) and on undefined `$account` in club subscriptions
- XLSX export temporary file was never deleted
- `/api/search` always returned 500 (wrong `Pageable` import in `SearchController` and `SearchDao`)
- `db:dump` crashed with DBAL 3 (`Connection::getUsername()` removed)
- OpenAPI spec: `getUsers` 404 response was emitted as a global component (stray parenthesis); single-resource GETs (lesson, location, price, user, users) had an empty `application/hal+json` schema (`Items` directly under `MediaType`); `nullable` was the string `"true"`
- `StaticPageController`: missing `City` import
- PHP 8.5 deprecation notices were printed in HTTP responses (old Symfony `ErrorHandler`)

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
