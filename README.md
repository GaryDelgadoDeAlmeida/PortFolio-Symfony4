# PortFolio-V3 - Copyright All Rights Reserved

Version Symfony 4.4 de mon portfolio.

## Installation

PHP 7.3 minimum

Dans le répertoire du projet : 

```bash
composer install
```

## Création de la base de données (terminal)

Créer la database :
```bash
symfony console doctrine:database:create
```


Générer les tables (pour la database) :
```bash
symfony console make:migration
```


Sauvegarder les modifications dans la database :
```bash
symfony console doctrine:migrations:migrate
```


## Configuration Apache Serveur Web

Dans le répertoire du project :
```bash
composer require symfony/apache-pack
```

Les configurations restantes (pour la mise en production) seront à faire à travers ce lien https://symfony.com/doc/current/setup/web_server_configuration.html

## Mise à jour

Symfony 4.4, étant une LTS (Long Terms Version), à régulièrement besoin d'être mmis à jour.
```bash
composer update
```

## Compression du style SASS en CSS
```bash
    sass --style compressed public/assets/sass/index.scss:public/assets/build/index.css
```