# PortFolio-V3

Version Symfony 4.4 de mon portfolio.

<b>Ce projet est, pour le moment, en phase de développement.</b>

# Installation

PHP 7.3 minimum

Dans le répertoire du projet : 
<code>composer install</code>

# Création de la base de données (terminal)

Créer la database :
<code>symfony console doctrine:database:create</code> ou <code>php bin/console doctrine:database:create</code>


Générer les tables (pour la database) :
<code>symfony console make:migration</code> ou <code>php bin/console make:migration</code>


Sauvegarder les modifications dans la database :
<code>symfony console doctrine:migrations:migrate</code> ou <code>php bin/console doctrine:migrations:migrate</code>


# Configuration Apache Serveur Web

Dans le répertoire du project :
<code>composer require symfony/apache-pack</code>

Les configurations restantes (pour la mise en production) seront à faire à travers ce lien https://symfony.com/doc/current/setup/web_server_configuration.html
