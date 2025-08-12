# Laravel X-Fer

[![Tests](https://img.shields.io/badge/tests-86%20passed-green)](https://github.com/projectsaturnstudios/laravel-x-fer)
[![Code Coverage](https://img.shields.io/badge/coverage-86.6%25-brightgreen)](https://github.com/projectsaturnstudios/laravel-x-fer)
[![Latest Version](https://img.shields.io/packagist/v/projectsaturnstudios/laravel-x-fer.svg?style=flat-square)](https://packagist.org/packages/projectsaturnstudios/laravel-x-fer)
[![License](https://img.shields.io/packagist/l/projectsaturnstudios/laravel-x-fer.svg?style=flat-square)](https://packagist.org/packages/projectsaturnstudios/laravel-x-fer)

Transfer files between servers using SFTP or AWS S3 with one line of code! Laravel X-Fer provides a clean, fluent API for moving files between different storage systems seamlessly.

## Features

- 🚀 **Fluent API** - Intuitive, chainable syntax
- 📁 **Multiple Storage Support** - SFTP, AWS S3, Local, and any Laravel filesystem
- 🔄 **Immutable Objects** - Safe, predictable data handling using Spatie Laravel Data
- 📡 **Event-Driven** - Built-in events for transfer lifecycle monitoring
- 🧪 **Thoroughly Tested** - 86 tests with 86.6% coverage of business logic
- 🏗️ **Contract-First Design** - Extensible architecture with clean interfaces
- ⚡ **Laravel Integration** - Service provider, facades, and container bindings included

## Installation

Install Laravel X-Fer using Composer:

```bash
composer require projectsaturnstudios/laravel-x-fer
```

Publish the configuration file:

```bash
php artisan vendor:publish --tag=xfer
```

## Quick Start

### Basic File Transfer

```php
use ProjectSaturnStudios\Xfer\Support\Facades\InitiateTransfer;
use ProjectSaturnStudios\Xfer\DTO\ReadableFileResource;
use ProjectSaturnStudios\Xfer\DTO\RecipientDetails;

// Transfer a file from local storage to S3
$result = InitiateTransfer::from(
    new ReadableFileResource('local', 'document.pdf', 'uploads')
)->to(
    new RecipientDetails('s3', 'document.pdf', 'backups')
)->transfer();

if ($result->success()) {
    echo "Transfer completed successfully!";
} else {
    echo "Transfer failed: " . $result->message();
}
```

### Fluent API Examples

```php
// Transfer between different storage systems
InitiateTransfer::from(new ReadableFileResource('sftp', 'data.csv', 'exports'))
    ->to(new RecipientDetails('local', 'imported-data.csv', 'imports'))
    ->transfer();

// Method chaining works in any order
InitiateTransfer::to(new RecipientDetails('s3', 'backup.sql'))
    ->from(new ReadableFileResource('local', 'database.sql'))
    ->transfer();

// Complex folder structures
InitiateTransfer::from(
    new ReadableFileResource('backup-server', 'report.xlsx', 'reports/2024/q4')
)->to(
    new RecipientDetails('client-ftp', 'quarterly-report.xlsx', 'deliveries/reports')
)->transfer();
```

## Architecture

Laravel X-Fer follows a contract-first design with clean separation of concerns:

```
┌─────────────────────────────────────────────────────────────────┐
│                    FileTransferOrchestrator                     │
│                         (Xfer)                                  │
├─────────────────────────────────────────────────────────────────┤
│  • Fluent API entry point                                      │
│  • Manages transfer request lifecycle                          │
│  • Immutable state management                                  │
└─────────────────────────────────────────────────────────────────┘
                                 │
                                 ▼
┌─────────────────────────────────────────────────────────────────┐
│                     TransferRequest                             │
├─────────────────────────────────────────────────────────────────┤
│  • Encapsulates source + destination                           │
│  • State validation (uninitialized → pending → ready)          │
│  • Stream resource management                                  │
└─────────────────────────────────────────────────────────────────┘
                                 │
                                 ▼
┌─────────────────────────────────────────────────────────────────┐
│                     TransferAction                              │
├─────────────────────────────────────────────────────────────────┤
│  • Handles actual file transfer                                │
│  • Event dispatching (start/success/failed)                    │
│  • Resource cleanup and error handling                         │
└─────────────────────────────────────────────────────────────────┘
```

### Core Components

- **ReadableFileResource** - Represents a source file with disk, path, and folder
- **RecipientDetails** - Represents the destination with disk, path, and folder  
- **TransferRequest** - Immutable transfer configuration with state management
- **TransferAction** - Executes the actual file transfer with event dispatching
- **TransferResult** - Success/failure result with exception details

## Configuration

The configuration file allows you to customize event names:

```php
// config/file-transfers.php
return [
    'started-event' => 'xfers.transfer.start',
    'finished-event' => 'xfers.transfer.finished', 
    'failed-event' => 'xfers.transfer.failed'
];
```

## Events

Laravel X-Fer dispatches events throughout the transfer lifecycle:

```php
// Listen for transfer events
Event::listen('xfers.transfer.start', function ($request) {
    Log::info('Transfer started', ['source' => $request->source->getFullPath()]);
});

Event::listen('xfers.transfer.finished', function ($request) {
    Log::info('Transfer completed successfully');
});

Event::listen('xfers.transfer.failed', function ($request, $exception) {
    Log::error('Transfer failed', ['error' => $exception->getMessage()]);
});
```

## Error Handling

Laravel X-Fer provides detailed error information through the result objects:

```php
$result = InitiateTransfer::from($source)->to($destination)->transfer();

if (!$result->success()) {
    $exception = $result->exception();
    $message = $result->message();
    
    // Log or handle the error appropriately
    Log::error('File transfer failed', [
        'message' => $message,
        'exception' => $exception?->getMessage(),
        'source' => $source->getFullPath(),
        'destination' => $destination->getFullPath()
    ]);
}
```

## Testing

Laravel X-Fer includes a comprehensive test suite built with Pest PHP:

```bash
# Run all tests
./vendor/bin/pest

# Run with coverage
./vendor/bin/pest --coverage

# Run specific test groups
./vendor/bin/pest --group=unit
./vendor/bin/pest --group=feature
```

### Test Coverage

The package maintains 86.6% test coverage focusing on:

- ✅ Core file transfer logic (TransferAction: 73.7%)
- ✅ All contract implementations (100%)
- ✅ DTO immutability and validation (TransferRequest: 100%)
- ✅ Fluent API functionality (Xfer orchestrator: 85.7%)
- ✅ Real file transfer between storage systems
- ✅ Success/failure result handling

*Note: Framework components (service providers, facades) are excluded from coverage as they're tested by Laravel itself.*

## Advanced Usage

### Custom Storage Disks

Configure custom disks in your `config/filesystems.php`:

```php
'disks' => [
    'client-sftp' => [
        'driver' => 'sftp',
        'host' => env('CLIENT_SFTP_HOST'),
        'username' => env('CLIENT_SFTP_USERNAME'),
        'password' => env('CLIENT_SFTP_PASSWORD'),
        'root' => '/uploads',
    ],
    
    'backup-s3' => [
        'driver' => 's3',
        'key' => env('BACKUP_AWS_ACCESS_KEY_ID'),
        'secret' => env('BACKUP_AWS_SECRET_ACCESS_KEY'),
        'region' => env('BACKUP_AWS_DEFAULT_REGION'),
        'bucket' => env('BACKUP_AWS_BUCKET'),
    ],
],
```

### Dependency Injection

Laravel X-Fer integrates seamlessly with Laravel's service container:

```php
use ProjectSaturnStudios\Xfer\Contracts\FileTransferOrchestratorInterface;

class DocumentService
{
    public function __construct(
        private FileTransferOrchestratorInterface $xfer
    ) {}
    
    public function backupDocument(string $filename): bool
    {
        $result = $this->xfer
            ->from(new ReadableFileResource('local', $filename, 'documents'))
            ->to(new RecipientDetails('backup-s3', $filename, 'document-backups'))
            ->transfer();
            
        return $result->success();
    }
}
```

## Requirements

- PHP 8.2+
- Laravel 11.0+
- Required packages:
  - `spatie/laravel-data ^4.0` - For immutable DTOs
  - `league/flysystem-sftp-v3 ^3.28` - For SFTP support
  - `league/flysystem-aws-s3-v3 ^3.28` - For S3 support

## Contributing

We welcome contributions! Please see our contributing guidelines for details.

1. Fork the repository
2. Create a feature branch
3. Write tests for your changes
4. Ensure all tests pass
5. Submit a pull request

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on recent changes.

## Security

If you discover any security-related issues, please email security@projectsaturnstudios.com instead of using the issue tracker.

## Credits

- [Project Saturn Studios](https://github.com/projectsaturnstudios)
- [All Contributors](../../contributors)

## License

Laravel X-Fer is open-sourced software licensed under the [MIT license](LICENSE.md).

---

**Made with ADHD by [Project Saturn Studios](https://projectsaturnstudios.com)**