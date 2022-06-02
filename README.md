# Import Liste

## 1. Créer les champs personalisés + configuration
Ce script PHP est à exécuter depuis la ligne de commande et va créer les champs personalisées, va configurer le pays par default, les monnaies disponibles... :
```shell
php configcivi.php <répertoire Wordpress>
```
p.ex. php configcivi.php /var/www
## 2. Importer le fichier Excel
Ce script PHP est à exécuter depuis la ligne de commande et va lire le fichier Excel ligne par ligne et créer les contacts :
```shell
php start.php <nom du fichier Excel> <répertoire Wordpress>
```
p.ex. php configcivi.php /tmp/liste2021.xlsx /var/www

