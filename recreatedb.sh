#!/bin/bash

php bin/console doctrine:database:drop --force
php bin/console doctrine:database:create

rm migrations/Version2019*.*
rm migrations/Version202*.*
rm migrations/Version203*.*

dbname=`grep -v '^ *#' .env.local | grep DATABASE_URL | grep '[^ ] *=' | sed 's:.*/::'`
dbcredentials=`sed 's/.*mysql:\/\/\(.*\)@.*/\1/' <<< $dbname`
dbuser=`cut -d: -f1 <<< $dbcredentials`
dbpassword=`cut -d: -f2 <<< $dbcredentials`

function runSql() {
  echo ''
  echo Running SQL scripts %~1
  echo ''
	mysql --user=$dbuser --password=$dbpassword -D $dbname < doc/sql/$1
}

runSql function_unaccent.sql

php bin/console make:migration
php bin/console doctrine:migrations:migrate --no-interaction
php bin/console doctrine:fixtures:load --no-interaction --group=MenuItemFixtures
php bin/console cache:clear

runSql cities.sql

echo ''
echo 'To add fake values :'
echo ''
echo 'php bin/console doctrine:fixtures:load --append --group=ClubsFixtures --group=AccountUserFixtures --group=UserClubLinkFixtures'
echo ''
echo ''
echo ''
echo 'To migrate real values :'
echo ''
echo 'php bin/console crm:migration --domainname=legacydomain.fr --dump=dump_src.sql'
echo ''
