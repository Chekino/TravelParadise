# Installation des dépendances

composer install

# Créer sa base de donnée MYSQL et Configurer la base de données dans .env

DATABASE_URL="mysql://root:root@127.0.0.1:3306/nom_de_la_base_de_donnees"

# Appliquer les migrations 

php bin/console doctrine:migrations:migrate

#Charger les fixtures pour insérer des utilisateurs par défaut

php bin/console doctrine:fixtures:load

Email	Mot de passe	Rôle
admin@site.com	adminpass	ROLE_ADMIN
user@site.com	userpass	ROLE_USER
guide@site.com	guidepass	ROLE_GUIDE


