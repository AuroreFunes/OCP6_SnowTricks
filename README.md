# OCP6_SnowTricks
OpenClassrooms - Projetc 6 - SnowTricks

It is a community site that allows users to create, edit and comment on snowboard tricks.

## Installation procedure


### Database

* Edit the ".env" file to fill in the connection information to your database :
> DATABASE_URL=

* Create the database structure using this command:
> php bin/console doctrine:migrations:migrate

* Create the first data from the Fixtures :
> php bin/console doctrine:fixtures:load