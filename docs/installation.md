---
title: "Installation"
description: "Install Laravel Model Preferences and publish its configuration and migration."
weight: 2
---

## Requirements

Laravel Model Preferences requires PHP 8.4 or later and Laravel 13.

## Install the package

Install the package with Composer:

```bash
composer require hosmelq/laravel-model-preferences
```

Run the installer to publish the configuration file and the shared table migration:

```bash
php artisan model-preferences:install
```

The installer offers to run the migrations. If you skip that step, run them yourself:

```bash
php artisan migrate
```

## Publish manually

To publish the files without the installer, run:

```bash
php artisan vendor:publish --tag="model-preferences-config"
php artisan vendor:publish --tag="model-preferences-migrations"
php artisan migrate
```

The published migration reads `model-preferences.stores.shared.connection` and `model-preferences.stores.shared.table`. Set those options before migrating if you want to customize the connection or table name. See [configuration](configuration).

## Next steps

Review the [configuration](configuration), then [define preferences on your first model](basic-usage/defining-preferences).
