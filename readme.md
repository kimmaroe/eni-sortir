## Sortir.com | ENI oct 2020

### Installation 
```
git clone https://github.com/gsylvestre/eni-sortir.git   
cd eni-sortir/  
composer install   
php bin/console doctrine:database:create   
php bin/console doctrine:migrations:migrate    
php bin/console doctrine:fixtures:load  
```
### Lancer les tests fonctionnels 
```
php bin/console doctrine:database:drop --env=test
php bin/console doctrine:database:create --env=test
php bin/console doctrine:migrations:migrate --env=test  
php bin/console doctrine:fixtures:load --env=test   
php ./bin/phpunit 
```

### Utilisateurs disponibles dans les fixtures
- yo@yo.com (mot de passe: yoyoyo)
- admin@admin.com (mot de passe: adminadmin)

Back-office accessible sur /admin