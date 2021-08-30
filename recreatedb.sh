#!/bin/bash

php bin/console doctrine:database:drop --force
php bin/console doctrine:database:create

rm migrations/Version2019*.*
rm migrations/Version202*.*
rm migrations/Version203*.*

dburl=`grep -v '^ *#' .env.local | grep DATABASE_URL | grep '[^ ] *=' | sed 's:.*=::'`
dbname=`sed 's:.*/::' <<< $dburl`
dbcredentials=`sed 's/.*mysql:\/\/\(.*\)@.*/\1/' <<< $dburl`
dbuser=`cut -d: -f1 <<< $dbcredentials`
dbpassword=`cut -d: -f2 <<< $dbcredentials`

function runSqlScript() {
  echo ''
  echo Running SQL scripts: $1
  echo ''
	mysql --user=$dbuser --password=$dbpassword -D $dbname < doc/sql/$1
}

function runSqlQuery() {
  echo ''
  echo Running SQL query: $1
  echo ''
	echo "$1" | mysql --user=$dbuser --password=$dbpassword -D $dbname
}

runSqlQuery "ALTER DATABASE $dbname CHARACTER SET utf8 COLLATE utf8_general_ci;"

runSqlScript function_unaccent.sql

php bin/console make:migration
php bin/console doctrine:migrations:migrate --no-interaction
# php bin/console doctrine:fixtures:load --no-interaction --group=MenuItemFixtures
php bin/console cache:clear

runSqlScript schema-update.sql
runSqlScript cities.sql
runSqlScript configuration_properties.sql

echo ''
echo 'To add fake values :'
echo ''
echo 'php bin/console doctrine:fixtures:load --append --group=AccountUserFixtures --group=ClubsFixtures --group=UserClubLinkFixtures'
echo ''
echo ''
echo ''
echo 'To migrate real values :'
echo ''
echo 'php bin/console crm:migration --domainname=legacydomain.fr --dump=dump_src.sql'
echo ''
