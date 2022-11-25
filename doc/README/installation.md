# Project 7 - Create a web service exposing an API

This project was realized for my PHP/Symfony developer training at Openclassrooms.

## Requirements
To be sure that the project will install successfully, you need to have :
```
- PHP 8.1
- Composer 2.3.4
- Git
- Prefer a computer based on Unix OS (Linux or Macos)
```

## Installation

1. First, clone the project :
```bash
git clone git@github.com:davy-beauzil/p7-api.git
cd p7-api
```

2. Create `.env.local` file from `.env` file and adapt your database configuration (`DATABASE_URL`)

3. Install composer dependencies
```bash
composer install
```

4. Initialize database
```bash
php bin/console doctrine:database:create
php bin/console doctrine:schema:create
```
5. Generate public and private keys for the LexikJWTAuthenticationBundle
```bash
php bin/console lexik:jwt:generate-keypair
php bin/console lexik:jwt:generate-keypair --env=test
```

6. Load fixtures
```bash
php bin/console doctrine:fixtures:load
```

7. You can now run this command and access to the documentation on [http://localhost:8000/api/doc](http://localhost:8000/api/doc)
```bash
symfony serve
```

## Run tests

You can run test with :
```baash
vendor/bin/phpunit
```


