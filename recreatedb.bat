@echo off

php bin/console doctrine:database:drop --force
php bin/console doctrine:database:create

del migrations\Version2019*.*
del migrations\Version202*.*
del migrations\Version203*.*

for /F "eol=# delims== tokens=1,*" %%a in (.env.local) do (
  if NOT "%%a"=="" if NOT "%%b"=="" if "%%a"=="DATABASE_URL" set CRM_%%a=%%b
)
rem CRM_DATABASE_URL = mysql://root:@127.0.0.1:3306/my_db
for %%a in (%CRM_DATABASE_URL:/= %) do set db_name=%%a
rem db_name = my_db


call:mysqlScript function_unaccent.sql

php bin/console make:migration
php bin/console doctrine:migrations:migrate --no-interaction
php bin/console doctrine:fixtures:load --no-interaction --group=MenuItemFixtures
php bin/console cache:clear

call:mysqlScript cities.sql

echo.
echo To add fake values :
echo.
echo php bin/console doctrine:fixtures:load --append --group=ClubsFixtures --group=AccountUserFixtures --group=UserClubLinkFixtures
echo.
echo.
echo.
echo To migrate real values :
echo.
echo php bin/console crm:migration --domainname=legacydomain.fr --dump=dump_src.sql
echo.

goto:eof



:mysqlScript
echo.
echo Running SQL scripts %~1
echo.
mysql -u root --password= -D %db_name% < doc\sql\%~1
goto:eof
