# CLAUDE.md

Guidance for coding agents working on this repository.

## What it is

CRM and public website of the *Cénacle Rémi Mollet* (Taekwonkido / Taekwondo / Hapkido clubs network).
Public pages (clubs, lessons, locations, prices, planning), a club search, user/account management and an admin configuration screen.
Served in production under `https://ceintureblanche.fr/crm/`.

## Stack

- PHP `>=8.4` (Composer platform pinned to `8.5.0`), Symfony **7.4 LTS**
- Doctrine ORM 3 / DBAL 4 / Migrations 4, MySQL/MariaDB (utf8mb4), `beberlei/doctrineextensions`
- Twig 3, JMS Serializer + Hateoas (API views), NelmioApiDoc + `zircote/swagger-php` (OpenAPI attributes)
- Symfony Mailer + Notifier, Rate Limiter, Lock (`LOCK_DSN=flock`), PhpSpreadsheet
- No frontend build: `package.json` lists Encore but there is no `webpack.config.js`; static assets are committed under `public_html/`

## Layout

- **Web root is `public_html/crm/`** (`extra.public-dir`), not `public/`. Front controller: `public_html/crm/index.php`.
  The app runs under the `/crm` URL prefix; Hateoas links hardcode `/crm/api/...`.
- `public_html/` (outside `crm/`) holds the **legacy PHP site** (`index.php`, `*.inc.php`, `clubs/`, `pres/`, …) — not Symfony.
- `src/` (namespace `App\`):
  - `Controller/` — web pages (Twig), route names prefixed `web_`. They often `forward()` to API controllers and decode the JSON.
  - `Controller/Api/` — JSON REST API under `/api`, route names prefixed `api_`, OpenAPI attributes (`OpenApi\Attributes as OA`).
  - `Controller/Debug/` — email preview (`/debug/email`).
  - `Entity/` — `Account` (login principal, `login`) → `User` (person) → `UserClubSubscribe` (user↔club link carrying club roles) → `Club` → `ClubLesson`, `ClubLocation`, `ClubPrice`, `ClubProperty`; plus `City`, `ConfigurationProperty`, history/audit entities.
  - `Model/` — API DTOs: `*Create` / `*Update` (input, validated) and `*View` (output, JMS/Hateoas).
  - `Repository/`, `Dao/SearchDao` (rights-aware search), `Service/` (`ClubService`, `UserService`, …).
  - `Security/` — `LoginFormAuthenticator`, `ApiAuthenticator` (`POST /api/login`), `LegacyPasswordHasher`, `ClubAccess`, `Roles` (constants).
  - `Validator/Constraints/`, `Twig/` (filters/functions), `Media/` (uploads in `<project>/media/`, gitignored), `Util/`.
- Routing: PHP attributes `#[Route]` on `src/Controller/`. Public identifiers in URLs are `uuid` (e.g. `/club/{uuid}`).
- Translations: `translations/messages.{fr,en}.yaml`, default locale `fr`, fallback `en`.

## Security model

- Authenticator manager, `main` firewall lazy, provider = `Account` by `login`, login throttling, remember-me, logout `app_logout`.
- **`access_control` and `role_hierarchy` are empty**: authorization is done in code with `#[IsGranted]` and `App\Security\ClubAccess` (club-scoped rights). Keep it that way unless asked; always check both when touching an endpoint.
- Roles: `ROLE_SUPER_ADMIN`, `ROLE_ADMIN`, `ROLE_CLUB_MANAGER`, `ROLE_TEACHER`, `ROLE_STUDENT`, `ROLE_USER` (see `Security/Roles.php`, club write roles = manager + teacher).
- Legacy passwords (`sha1:` prefix, salted SHA-1 from the old site) go through `legacy_hasher` and are rehashed on successful login (`AccountRepository::upgradePassword`).

## Multi-club content

- Per-club templates live in `templates/club/<club_uuid>/`.
- `club.html.twig` includes `club/<uuid>/home.html.twig` when it exists, else `club/default-home.html.twig`.
- `/club/{uuid}/sc/{code}` (`web_club_static_custom`) renders `club/<uuid>/<code>.html.twig` (sponsors, instructor, …).

## Configuration

- `.env` is committed and holds **no secrets**; real values go in `.env.local` (gitignored) or `.env.local.php` in production.
- Env vars: `APP_ENV`, `APP_SECRET`, `DATABASE_URL` (`mysql://…`), `MAILER_DSN`, `LOCK_DSN`, `CORS_ALLOW_ORIGIN`, `VAR_DUMPER_SERVER`, `TEST_TOKEN`.
- Never commit credentials, dumps (`dump*.sql`) or `.env.local*`.

## Local development

```bash
composer install
cp .env .env.local            # then set DATABASE_URL and MAILER_DSN
./recreatedb.sh               # recreatedb.bat on Windows
php bin/console crm:migration --domainname=<legacy domain> --dump=<legacy dump.sql>
php bin/console cache:clear
```

- `recreatedb` **drops the database and deletes `migrations/Version*`**, regenerates a migration from the entities (`make:migration`), migrates, then loads `doc/sql/*.sql` (unaccent function, schema tweaks, cities, configuration properties).
- `migrations/` is **gitignored**: the schema source of truth is the entity mapping, migrations are generated per environment.
- Console commands: `db:dump <dir>` (MariaDB dump, password via `MYSQL_PWD`), `crm:migration` (import from the legacy site dump, downloads club logos).
- Translations: `php bin/console translation:extract --force fr` (and `en`).
- API doc JSON: `/crm/api/doc.json`; `swagger-generator.php` regenerates `public_html/crm/swagger-config.json`.
- Tests: none yet (`tests/bootstrap.php` only). `php bin/phpunit`; `phpunit.xml.dist` is in the old format.

## Preprod and production

Hosting: **o2switch** (cPanel), one account for both environments. Each environment is a clone of this repository, deployed with `git pull`:

| Environment | Directory on the server |
|-------------|-------------------------|
| preprod     | `~/sites/crm.preprod`   |
| prod        | `~/sites/crm.prod`      |

Always deploy to **preprod first**, check it, then deploy the same commit to prod.

### SSH access

```bash
ssh -p 22 sc2cenacle@fraise.o2switch.net
```

- Authenticate with an SSH key (with passphrase), never a password or a stored token.
- Each clone has its own `.env.local` / `.env.local.php` (database, secret, mailer): never copy one environment's file over the other.
- In each clone, run `git config core.fileMode false` once (ignore chmod changes).

### Deploy

From your machine: commit and push to `master` on GitHub (`git@github.com:CenacleRemiMollet/crm.git`). Then on the server, in `~/sites/crm.preprod` first, then in `~/sites/crm.prod`:

```bash
ssh -p 22 sc2cenacle@fraise.o2switch.net
cd ~/sites/crm.preprod                               # then ~/sites/crm.prod

php bin/console db:dump backup                       # backup the database first
git pull origin master
composer install --no-dev --optimize-autoloader
composer dump-env prod                               # writes .env.local.php
php bin/console doctrine:migrations:migrate --no-interaction
APP_ENV=prod APP_DEBUG=0 php bin/console cache:clear
```

- Schema changes: since `migrations/` is not versioned, generate the migration **on the server** before migrating
  (`php bin/console make:migration`). `make:migration` needs the dev dependencies (MakerBundle): run `composer install`
  (with dev) for that step, then re-run `composer install --no-dev --optimize-autoloader`. Review the generated SQL before migrating.
- Never run `recreatedb` in production: it drops the database.
- Rollback: `git checkout <previous commit>` on the server, `composer install --no-dev --optimize-autoloader`, `cache:clear`, and restore the dump if the schema changed.

## Conventions

- Match the surrounding file: indentation is mixed (tabs in most controllers/commands, 4 spaces elsewhere) — don't reformat unrelated code.
- Changelog: `CHANGELOG.md`, Keep a Changelog format, written by hand (sections Added / Changed / Deprecated / Removed / Fixed / Security).
- Commit directly on `master` is the current practice (no PR). Never force-push without an explicit request.
- No GitHub Actions workflow is used. **Any new `.github/workflows/*` file is suspicious** (the repo was compromised in Aug–Sep 2025 by commits adding/removing a crypto-miner workflow; history was reset to `b8d65df`).

## Known issues (as of 2026-09-29)

- `src/Emails/EmailFactory.php` and `Controller/Debug/EmailsPreviewController.php` still type-hint `\Swift_Mailer` (SwiftMailer is no longer installed) → password-request emails and `/debug/email` sending are broken; migrate to `MailerInterface`.
- `src/Security/{LoginFormAuthenticator,ApiAuthenticator}.php` import removed classes (`Symfony\Component\Security\Core\Security`, `Symfony\Component\Security\Guard\PasswordAuthenticatedInterface`).
- `config/services.yaml` declares `App\XClientIdEventSubscribe` (real class: `App\EventSubscriber\XClientIdEventSubscribe`).
- `Repository/MenuItemRepository.php` references a non-existent `App\Entity\MenuItem`.
- `DEVELOPER.md` / `DEVOPS.md` are partly outdated (WAMP + PHP 7.2, `MAILER_URL`).
