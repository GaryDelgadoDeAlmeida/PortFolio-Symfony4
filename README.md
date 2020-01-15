# PortFolio-Symfony4

Version Symfony 4 de mon portfolio, développé sous l'architecture MVC.

<b>Ce projet est, pour le moment, en phase de développement.</b>

# Création de la la base de données (terminal)

<code>symfony console doctrine:database:create</code> ou <code>php bin/console doctrine:database:create</code>

<code>symfony console make:migration</code> ou <code>php bin/console make:migration</code>

<code>symfony console doctrine:migrations:migrate</code> ou <code>php bin/console doctrine:migrations:migrate</code>

# Configuration Apache Serveur Web

<code>composer require symfony/apache-pack</code>

Les configurations restantes seront à faire à travers ce lien https://symfony.com/doc/current/setup/web_server_configuration.html
