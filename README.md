# OCP6_SnowTricks
OpenClassrooms - Projetc 6 - SnowTricks

It is a community site that allows users to create, edit and comment on snowboard tricks.

## Prerequisites

You must have PHP 7.x and a database that you can manage freely. You must also have Composer installed and have a terminal to use the command lines.


## Installation procedure

First, copy all the project files.

### Dependencies

Go to the project directory to launch the installation of Symfony and its dependencies :
> composer install


### Symfony server

You have to make your database engine work.

Then, go to the project directory to start the Symfony server with the command :
> .\symfony.exe server:start


### Database

* Edit the ".env" file to fill in the connection information to your database :
> DATABASE_URL=

* Create the database using this command :
> php bin/console doctrine:database:create

* Then create the database structure by launching the migrations :
> php bin/console doctrine:migrations:migrate

* Create the first data from the Fixtures :
> php bin/console doctrine:fixtures:load


### Website Url

For the site to work properly, you must indicate in the .env file what its URL is. Change these two lines with your own information :

> SITE_BASE_SCHEME=http
> SITE_BASE_HOST=localhost:8000


### Mailer

For your site to be able to send e-mail, you must set up the SMTP mailer correctly.
Complete these two lines in .env file :
> MAILER_DSN=smtp://user:pass@smtp.example.com:port
> ADMIN_EMAIL=your-mail@example.com