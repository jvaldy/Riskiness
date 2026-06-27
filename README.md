# Riskiness

Application web Symfony pensee pour evoluer par modules independants.

## Structure

- `webapp/` : application Symfony
- `Docs/important.txt` : commandes utiles, points d'attention et notes de deploiement
- `logo/` : ressources graphiques

## Pre requis

- PHP 8.1+
- Composer
- Git

## Installation depuis GitHub

1. Cloner le depot :
   ```bash
   git clone <URL_DU_DEPOT_GITHUB>
   ```
2. Entrer dans le projet :
   ```bash
   cd Riskiness
   ```
3. Aller dans l'application Symfony :
   ```bash
   cd webapp
   ```
4. Choisir le mode :
   - Dev local : suivre la section `Installation locale`.
   - Prod : suivre la section `Deploiement prod`.

## Installation locale

1. Aller dans l'application :
   ```bash
   cd webapp
   ```
2. Creer le fichier d'environnement local a partir du template :
   ```bash
   copy .env.example .env.local
   ```
3. Modifier `webapp/.env.local` avec les valeurs locales si besoin.
   - Ce fichier est le seul endroit a modifier pour les secrets ou overrides locaux.
   - Ne pas modifier `webapp/.env.example` pour la machine locale.
4. Installer les dependances :
   ```bash
   composer install
   ```
5. Verifier l'etat de Symfony :
   ```bash
   php bin/console about
   ```
6. Lancer le serveur local :
   ```bash
   php -S 127.0.0.1:8000 -t public
   ```

## Emplacement des modifications

- `webapp/.env.example` : template a versionner et a partager.
- `webapp/.env.local` : configuration locale privee, non committee.
- `webapp/.env.prod` : configuration de prod de reference, sans secret reel.
- `webapp/config/packages/framework.yaml` : lecture du `APP_SECRET` par Symfony.
- `webapp/public/index.php` : point d'entree HTTP de l'application.

## Deploiement prod

1. Partir du depot clone depuis GitHub, puis aller dans `webapp`.
2. Preparer les valeurs de production dans l'environnement cible.
   - Definir `APP_ENV=prod`.
   - Definir `APP_DEBUG=0`.
   - Definir `APP_SECRET` dans l'environnement du serveur ou dans `webapp/.env.local` cote serveur.
3. Si tu deployes avec les fichiers `.env`, partir de `webapp/.env.prod` comme base de reference.
4. Installer les dependances cote serveur :
   ```bash
   cd webapp
   composer install --no-dev --optimize-autoloader
   ```
5. Compiler les fichiers `.env` pour la production si ce mode est utilise :
   ```bash
   composer dump-env prod
   ```
6. Vider le cache de production :
   ```bash
   php bin/console cache:clear --env=prod
   ```
7. Verifier l'application :
   ```bash
   php bin/console about --env=prod
   ```
8. Redemarrer le service web si necessaire apres le deploiement.

## Notes

- Aucun secret reel ne doit rester dans un fichier versionne.
- Si un secret a deja ete pousse, il faut le regenerer cote production.
