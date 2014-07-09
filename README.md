Administrator Generate Config
=============================

A simple model configuration generator for Laravel Administrator https://github.com/FrozenNode/Laravel-Administrator/ 

This package provides an artisan command to generate skeleton model configuration files from model classes suitable for [Laravel Administrator](http://administrator.frozennode.com/).  

This is very much a work in progress.  The output skeleton model configuration files should be viewed as a step-saver and it is expected that they are reviewed and edited before deploying.

## Usage

To use as a Composer package with Laravel 4, add this to your composer.json:

```json
"yottaram/administrator-config": "dev-master"
```

And run `composer update`.  When it is installed, register the service provider in `app/config/app.php` in the `providers` array:

```php
'providers' => array(
        'Yottaram\AdministratorConfig\AdministratorConfigServiceProvider',
)        
```

This creats an artisan command and can be used with `php artisan administrator:config --help`

You will be prompted for output directory and asked to confirm each model config creation.  To skip confirmation use `--no-prompt`.  An existing model configuration will never be overwritten.
