# Laravel X-Fer

[![Latest Version on Packagist](https://img.shields.io/packagist/v/projectsaturnstudios/laravel-x-fer.svg?style=flat-square)](https://packagist.org/packages/projectsaturnstudios/laravel-x-fer)
[![Total Downloads](https://img.shields.io/packagist/dt/projectsaturnstudios/laravel-x-fer.svg?style=flat-square)](https://packagist.org/packages/projectsaturnstudios/laravel-x-fer)
[![Code Coverage](https://img.shields.io/badge/coverage-14%25-red.svg?style=flat-square)](https://github.com/projectsaturnstudios/laravel-x-fer)
[![License](https://img.shields.io/packagist/l/projectsaturnstudios/laravel-x-fer.svg?style=flat-square)](https://packagist.org/packages/projectsaturnstudios/laravel-x-fer)
<!-- [![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/projectsaturnstudios/laravel-x-fer/run-tests.yml?branch=main&label=Tests)](https://github.com/projectsaturnstudios/laravel-x-fer/actions/workflows/run-tests.yml) -->

A simple Laravel package that lets you build streaming file transfers between two defined Laravel filesystem drivers using a factory pattern for one-line transfers. Perfect for ETL processes, data migrations, and cross-server file operations.

## Requirements

- Laravel 11 or greater
- PHP 8.2 or greater

## Installation

You can install the package via composer:

```bash
composer require projectsaturnstudios/laravel-x-fer
```

After installation, you can optionally publish the configuration file and migration:

```bash
php artisan vendor:publish --tag=xfer-config
php artisan vendor:publish --tag=xfer-migrations
```

Run the migrations if you plan to use the logging features:

```bash
php artisan migrate
```

## Configuration

After publishing the config file, you can modify `config/file-transfers.php` to customize the batch processing behavior:

```php
return [
    /*
    |--------------------------------------------------------------------------
    | File Transfer Batch Driver
    |--------------------------------------------------------------------------
    |
    | This option controls the batch driver that will be used to process file
    | transfers. You may set this to any of the drivers provided by the
    | package or create your own custom driver that implements the
    | ProjectSaturnStudios\Xfer\Contracts\BatchDriver interface.
    |
    | Supported: "sync", "bus", "concurrency", "chain"
    |
    */

    'batch_processing' => [
        'driver' => env('XFER_BATCH_DRIVER', 'sync'),
        'drivers' => [
            'sync' => ProjectSaturnStudios\Xfer\Drivers\BatchProcessing\SyncBatchDriver::class,
            'bus' => ProjectSaturnStudios\Xfer\Drivers\BatchProcessing\BusBatchDriver::class,
            'chain' => ProjectSaturnStudios\Xfer\Drivers\BatchProcessing\ChainBatchDriver::class,
            'concurrency' => ProjectSaturnStudios\Xfer\Drivers\BatchProcessing\ConcurrencyBatchDriver::class,
        ],
    ],

    'folder_associations' => [
        'production' => [
            'sftp' => 'production',
            's3-processed' => 'production/processed',
        ],
        'staging' => [
            'sftp' => 'staging',
            's3-processed' => 'staging/processed',
        ],
        'archive' => [
            'sftp' => 'archive',
            's3-processed' => 'archive/processed',
        ],
    ],
];
```

## Usage

### One-off Transfer

Perform a simple file transfer between two storage disks:

```php
use ProjectSaturnStudios\Xfer\Support\Facades\CopyFile;

$copied = CopyFile::from('sftp', 'folder', 'doc.csv')
    ->to('local', 'folder', 'saved-doc.csv')
    ->transfer();
```

### Transfer with Logging

Add event sourcing and audit trails to your transfers:

```php
$results = CopyFile::from('sftp', 'folder', 'doc.csv')
    ->to('local', 'folder', 'saved-doc.csv')
    ->withLogging()
    ->transfer();
```

### Transfer Multiple Files

Use batch processing for multiple file transfers:

```php
use ProjectSaturnStudios\Xfer\Support\Facades\BatchTransfer;
use ProjectSaturnStudios\Xfer\DTO\Transfers\FileObject;

$batch = BatchTransfer::driver('concurrency');
foreach($transfers as $transfer) {
    $batch = $batch->addTransfer($transfer['source'], $transfer['destination']);
}
$results = $batch->dispatch();
```

### Transfer Multiple Files with Logging

Combine batch processing with logging:

```php
use ProjectSaturnStudios\Xfer\Support\Facades\BatchTransfer;
use ProjectSaturnStudios\Xfer\DTO\Transfers\FileObject;

$batch = BatchTransfer::driver('concurrency');
foreach($transfers as $transfer) {
    $batch = $batch->addTransfer($transfer['source'], $transfer['destination']);
}
$results = $batch->withLogging()->dispatch();
```

## Batch Processing Drivers

Laravel X-Fer provides four built-in batch processing drivers:

### 1. Sync Driver
Processes transfers synchronously (one after another). Best for small batches or when order matters.

### 2. Bus Driver
Uses Laravel's job bus to queue transfers. Ideal for background processing and decoupling from the main request.

### 3. Chain Driver
Chains transfers using Laravel's job chaining. Useful when transfers need to happen in sequence.

### 4. Concurrency Driver
Processes transfers concurrently using Laravel 11's new concurrency features. Perfect for large batches where parallel processing improves performance.

### Custom Drivers

You can create custom batch drivers by extending the base driver class:

```php
use ProjectSaturnStudios\Xfer\Drivers\BatchProcessing\BatchProcessingDriver;

class CustomBatchDriver extends BatchProcessingDriver
{
    public function process(array $items, bool $use_logging = false): array|bool
    {
        // Your custom batch processing logic
        foreach ($items as $item) {
            // Process each transfer
        }

        return $results;
    }
}
```

Register your custom driver in your service provider:

```php
use ProjectSaturnStudios\Xfer\Support\Facades\BatchManager;

BatchManager::extend('custom', function ($app) {
    return new CustomBatchDriver();
});
```

Alternatively, after publishing the config file, you can replace any of the built-in drivers in the `batch_processing.drivers` array:

```php
'drivers' => [
    'sync' => \App\Drivers\MyCustomSyncDriver::class,
    // ... other drivers
],
```

## API Reference

### CopyFile Facade

#### `from(string $disk, string $folder, string $path): static`
Define the source file location.

#### `to(string $disk, string $folder, string $path): static`
Define the destination file location.

#### `withLogging(bool $enabled = true): static`
Enable event sourcing and logging for the transfer.

#### `transfer(): bool|TransferResult`
Execute the file transfer. Returns `bool` for simple transfers, `TransferResult` for logged transfers.

### BatchTransfer Facade

#### `driver(?string $name = null): static`
Set the batch processing driver.

#### `addTransfer(FileObject $source, FileObject $destination): static`
Add a transfer to the batch.

#### `addTransfers(array $transfers): static`
Add multiple transfers to the batch.

#### `withLogging(): static`
Enable logging for all transfers in the batch.

#### `dispatch(): array|bool`
Execute all transfers in the batch.

## Testing

```bash
composer test
```

## Credits

- [Angel Gonzalez](https://github.com/projectsaturnstudios)
