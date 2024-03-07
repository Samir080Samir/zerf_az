<img width="150" src="public/npa/ticar-x.png">

### PROJECTS OF AZERBAIJAN MANAGEMENT SYSTEMS

## Install

Copy the .env.example file to .env and set up your application, then run the command

```bash
composer install
```

Then to run the application (locally) run the command

```bash
php artisan serve
```

Create tables

```bash
php artisan migrate
```

Create permissions

```bash
php artisan migrate:fresh --seed --seeder=PermissionSeeder
```

Create settings and other module data

```bash
php artisan module:seed
```

ONLY FOR DEV!!!!

```bash
php artisan db:seed --class=SeedDevPermissions
```
