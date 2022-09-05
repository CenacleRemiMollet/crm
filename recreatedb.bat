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

call:mysqlQuery "ALTER DATABASE %db_name% CHARACTER SET utf8 COLLATE utf8_general_ci;"

call:mysqlScript function_unaccent.sql

php bin/console make:migration
php bin/console doctrine:migrations:migrate --no-interaction
rem php bin/console doctrine:fixtures:load --no-interaction --group=MenuItemFixtures
php bin/console cache:clear

call:mysqlScript schema-update.sql
call:mysqlScript cities.sql
call:mysqlScript configuration_properties.sql

echo.
echo To add fake values :
echo.
echo php bin/console doctrine:fixtures:load --append --group=AccountUserFixtures --group=ClubsFixtures --group=UserClubLinkFixtures
echo.
echo.
echo.
echo To migrate real values :
echo.
echo php bin/console crm:migration --domainname=legacydomain.fr --dump=dump-20220701.sql
echo.

goto:eof



:mysqlScript
echo.
echo Running SQL scripts: %~1
mysql -u root --password= -D %db_name% < doc\sql\%~1
goto:eof

:mysqlQuery
echo.
echo Running SQL query: %~1
echo %~1 | mysql -u root --password= -D %db_name%
goto:eof