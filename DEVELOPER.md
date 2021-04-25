# Windows developer


## Required softwares

Install the following softwares (keep order) :

- Git : https://git-scm.com/download
- Wamp : http://www.wampserver.com/
- Composer : https://getcomposer.org/download/  (select php 7.2.x)
- Symfony : https://symfony.com/download
- ER/Builder : https://soft-builder.com/en/downloads/ERBuilder_Install.zip
- Eclipse PHP : https://www.eclipse.org/pdt/

If you have a error message like this : "MSVCR110.dll is missing", download the fix from https://www.microsoft.com/fr-FR/download/details.aspx?id=30679


### Configure installed software

Add into your `PATH` php and MySQL.

Into Windows 10 :
* open Explore : `Windows` + `E`
* Right click on `This PC` > `Properties`  (French : `Ce PC` > `Propriétés`)
* `Advanced system settings`  (French : `Paramètres système avancés`)
* `Environment variables...`  (French : `Varibles d'environnement...`)
* Update the `PATH` and add 2 paths (replace `<your wamp folder>` by the real value) :
  * `<your wamp folder>\bin\php\php7.2.14`  (ex: `c:\wamp64\bin\php\php7.2.14` )
  * `<your wamp folder>\bin\mysql\mysql5.7.24\bin`  (ex: `c:\wamp64\bin\mysql\mysql5.7.24\bin` )

Restart Eclipse.


## Prepare your environment

### Configure Git

Ignore `chmod` modification :

```
git config core.fileMode false
```


### Sources

In a new folder (ex: c:\projects), with a bash or cmd, retreive the project :

```
git clone https://github.com/CenacleRemiMollet/crm.git
```

Download all libraries :

```
cd crm
composer install
```

Run wamp64.

In the crm folder project, copy the `.env` to `.env.local`. Edit it (add or replace if necessary) :

```
DATABASE_URL=mysql://root:@127.0.0.1:3306/my_db

MAILER_URL=smtp://your-smtp-host:25?encryption=tls&auth_mode=login&username=&password=
```

Replace `my_db` by your database name and `your-smtp-host` by the SMTP domain name.


### Configure Wamp

In the wamp64 folder, rename the `www` folder to (for example) `www_origin`.

Create a symbolic link to the crm project : with a `cmd`, in the wamp folder :

```
mklink /D www <path_to_crm_project>\public_html
```

### Initialize the database

In the crm folder project, with cmd :

```
recreatedb
php bin/console crm:migration --domainname=<legacy domain> --dump=dump_src.sql
```

Inspect the result with a browser : http://localhost/phpmyadmin/  (user : root, password : )


## Update your environment

Update the project. In the root folder project, do :

```
git pull
composer install
```

Ensure Wamp64 is running.

Recreate / update the database :

```
recreatedb
php bin/console crm:migration --domainname=<legacy domain> --dump=dump_src.sql
```


# Update Translations

```
php bin/console translation:update --dump-messages --force fr
php bin/console translation:update --dump-messages --force en
```

# Push to production

In the production server :

```
git pull
composer install
php bin/console make:migration
php bin/console doctrine:migrations:migrate
```

# Cache

Sometimes, it's necessary to clear the cache : 

```
php bin/console cache:clear
```


# Swagger

Swagger is available  : http://localhost/swagger/index.html


# Email

To debug the renderer, try this : http://localhost/debug/email


# Database

## PDM

![PDM](/doc/database.jpg)

