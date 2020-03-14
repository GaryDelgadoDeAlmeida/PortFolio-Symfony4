# PortFolio-V3

Version Symfony 4.4 de mon portfolio.

<b>Ce projet est, pour le moment, en phase de développement.</b>

# Installation

PHP 7.3 minimum

Dans le répertoire du projet : 

```bash
composer install
```

# Création de la base de données (terminal)

Créer la database :
```bash
symfony console doctrine:database:create
```
ou 

```bash
php bin/console doctrine:database:create
```


Générer les tables (pour la database) :
```bash
symfony console make:migration
```
ou 
```bash
php bin/console make:migration
```


Sauvegarder les modifications dans la database :
```bash
symfony console doctrine:migrations:migrate
```
ou 

```bash
php bin/console doctrine:migrations:migrate
```


# Configuration Apache Serveur Web

Dans le répertoire du project :
```bash
composer require symfony/apache-pack
```

Les configurations restantes (pour la mise en production) seront à faire à travers ce lien https://symfony.com/doc/current/setup/web_server_configuration.html

# Mise à jour

Symfony 4.4, étant une LTS (Long Terms Version), à régulièrement besoin de m'être à jour ses packages. Par conséquent, dans le répertoire du projet, en ligne de commande :
```bash
composer update
```

Il mettra automatiquement à le fichier composer.lock et composer.json qu'il faudra commit et push.
