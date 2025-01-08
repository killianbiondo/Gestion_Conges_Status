# Projet Symfony Docker
Ce projet est une application Symfony fonctionnant dans un environnement Docker. Il inclut des services pour Nginx, PHP, MariaDB, et phpMyAdmin.

## Prérequis
- Docker
- Docker Compose

## Démarrage
### Cloner le dépôt et entrer dans le répertoire du projet
```sh
git clone https://github.com/El-Tome/tp_gestion_conges_status
cd tp_gestion_conges_status
```

### Créer un fichier .env en suivant le modèle .env.example
2 fichiers à copier : 
- `tp_gestion_conges_status/.env.local`
- `tp_gestion_conges_status/public/.env.local`

### Construire les conteneurs Docker et démarrer le projet
```sh
docker compose up -d --build
```

### Entrer dans le conteneur PHP

```sh
docker exec -it php-symfony-gestion_conges_stats bash
```

Et exécuter
```sh
composer install
sudo apt install nodejs npm -y
npm install
```

## Accéder à l'application
phpMyAdmin : http://localhost:8080
Application Symfony : http://localhost:80

## Structure du projet
- public/ : Contient l'application Symfony.
- nginx.conf : Fichier de configuration de Nginx.
- compose.yaml : Fichier de configuration Docker Compose.

## Commandes utiles
- Entrer dans le conteneur :
```sh
docker exec -it php-symfony-gestion_conges_stats bash
```

- Arrêter les conteneurs :
```sh
docker compose down
```

- Ajouter un jeu de données :
```sh
symfony console doctrine:fixtures:load
```

## Autres informations
version de Symfony : 7.1.5
version de PHP : 8.2
version de MariaDB : 11.5

## Auteur
[Tom Chaumette](https://github.com/El-Tome)