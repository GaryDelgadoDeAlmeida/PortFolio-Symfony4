# PortFolio-Symfony4

Version Symfony 4 de mon portfolio, précédemment développé en architecture MVC. 

Sur cette nouvelle version :
  - un système d'administration a été developpé pour facilité la gestion des contenus (modification, ajout, suppression) du site web
  - changement du design de l'onglet About
  - suppression de l'onglet News, jugé inutile dans le contexte de ce site web et déplacé dans l'espace admin a but personnelle
  - Un système d'upload d'image
  - Un système de pagination pour les projets
  
Ce site est en encore en phase de développement

# Commande Création Base de données

<code>symfony console doctrine:database:create</code> ou <code>php bin/console doctrine:database:create</code>

<code>symfony console make:migration</code> ou <code>php bin/console make:migration</code>

<code>symfony console doctrine:migrations:migrate</code> ou <code>php bin/console doctrine:migrations:migrate</code>

# Configuration Apache Serveur Web

<code>composer require symfony/apache-pack</code>

Les configurations restantes seront à faire à travers ce lien https://symfony.com/doc/current/setup/web_server_configuration.html
