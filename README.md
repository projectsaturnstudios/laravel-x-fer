# Laravel X-Fer

Transfer files between two remote servers with one-line!

[![Latest Version on Packagist](https://img.shields.io/packagist/v/projectsaturnstudios/laravel-x-fer.svg?style=flat-square)](https://packagist.org/packages/projectsaturnstudios/laravel-x-fer)
[![Total Downloads](https://img.shields.io/packagist/dt/projectsaturnstudios/laravel-x-fer.svg?style=flat-square)](https://packagist.org/packages/projectsaturnstudios/laravel-x-fer)

Require Laravel X-Fer using [Composer](https://getcomposer.org):

```bash
composer require projectsaturnstudios/laravel-x-fer
```

Publish the config file:

```bash
php artisan vendor:publish --tag=xfer.config
```

To Use


```php
StreamFile::from($source_file, $source_disk)
    ->to($destination_file, $destination_disk);

stream_from($source_file, $source_disk)
    ->to($destination_file, $destination_disk);

```
