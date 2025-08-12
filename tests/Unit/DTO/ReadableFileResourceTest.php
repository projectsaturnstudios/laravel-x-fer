<?php

use ProjectSaturnStudios\Xfer\DTO\ReadableFileResource;
use ProjectSaturnStudios\Xfer\Contracts\ReadableFileResourceInterface;

describe('ReadableFileResource', function () {
    test('can be instantiated with all parameters', function () {
        $resource = new ReadableFileResource('s3', 'document.pdf', 'uploads');
        
        expect($resource->getDisk())->toBe('s3');
        expect($resource->getPath())->toBe('document.pdf');
        expect($resource->getFolder())->toBe('uploads');
    });

    test('can be instantiated with minimal parameters', function () {
        $resource = new ReadableFileResource();
        
        expect($resource->getDisk())->toBeNull();
        expect($resource->getPath())->toBeNull();
        expect($resource->getFolder())->toBeNull();
    });

    test('disk method returns new instance with updated disk', function () {
        $original = new ReadableFileResource();
        $updated = $original->disk('sftp');
        
        expect($updated)->not->toBe($original);
        expect($updated->getDisk())->toBe('sftp');
        expect($original->getDisk())->toBeNull();
    });

    test('path method returns new instance with updated path', function () {
        $original = new ReadableFileResource('s3');
        $updated = $original->path('data.csv');
        
        expect($updated)->not->toBe($original);
        expect($updated->getPath())->toBe('data.csv');
        expect($updated->getDisk())->toBe('s3'); // Preserved
        expect($original->getPath())->toBeNull();
    });

    test('folder method returns new instance with updated folder', function () {
        $original = new ReadableFileResource('local', 'file.txt');
        $updated = $original->folder('exports');
        
        expect($updated)->not->toBe($original);
        expect($updated->getFolder())->toBe('exports');
        expect($updated->getDisk())->toBe('local'); // Preserved
        expect($updated->getPath())->toBe('file.txt'); // Preserved
    });

    test('getFullPath constructs correct path with folder', function () {
        $resource = new ReadableFileResource('s3', 'report.pdf', 'monthly-reports');
        
        expect($resource->getFullPath())->toBe('monthly-reports/report.pdf');
    });

    test('getFullPath returns just filename without folder', function () {
        $resource = new ReadableFileResource('local', 'simple.txt');
        
        expect($resource->getFullPath())->toBe('simple.txt');
    });

    test('getFullPath returns null when no path', function () {
        $resource = new ReadableFileResource('s3', null, 'folder');
        
        expect($resource->getFullPath())->toBeNull();
    });

    test('read method returns resource stream', function () {
        Storage::fake('test');
        Storage::disk('test')->put('uploads/test.txt', 'file content');
        
        $resource = new ReadableFileResource('test', 'test.txt', 'uploads');
        $stream = $resource->read();
        
        expect($stream)->toBeResource();
        
        $content = stream_get_contents($stream);
        expect($content)->toBe('file content');
        
        fclose($stream);
    });

    test('read method throws exception when disk not set', function () {
        $resource = new ReadableFileResource(null, 'file.txt');
        
        expect(fn() => $resource->read())
            ->toThrow(DomainException::class, 'Both disk and path must be set');
    });

    test('read method throws exception when path not set', function () {
        $resource = new ReadableFileResource('s3');
        
        expect(fn() => $resource->read())
            ->toThrow(DomainException::class, 'Both disk and path must be set');
    });

    test('implements ReadableFileResourceInterface', function () {
        $resource = new ReadableFileResource();
        
        expect($resource)->toBeInstanceOf(ReadableFileResourceInterface::class);
    });

    test('extends Spatie Data', function () {
        $resource = new ReadableFileResource('local', 'test.txt', 'folder');
        
        expect($resource)->toBeInstanceOf(\Spatie\LaravelData\Data::class);
    });

    test('fluent chaining works correctly', function () {
        $resource = (new ReadableFileResource())
            ->disk('backup-s3')
            ->folder('daily-backups')
            ->path('database.sql');
        
        expect($resource->getDisk())->toBe('backup-s3');
        expect($resource->getFolder())->toBe('daily-backups');
        expect($resource->getPath())->toBe('database.sql');
        expect($resource->getFullPath())->toBe('daily-backups/database.sql');
    });

    test('handles complex file paths', function () {
        $resource = new ReadableFileResource(
            'enterprise-s3', 
            'Q4-2024-financial-report.xlsx', 
            'reports/financial/quarterly'
        );
        
        expect($resource->getFullPath())->toBe('reports/financial/quarterly/Q4-2024-financial-report.xlsx');
    });

    test('immutability is preserved', function () {
        $original = new ReadableFileResource('original-disk', 'original.txt', 'original-folder');
        
        // Multiple operations
        $original->disk('new-disk');
        $original->path('new.txt');
        $original->folder('new-folder');
        
        // Original should be unchanged
        expect($original->getDisk())->toBe('original-disk');
        expect($original->getPath())->toBe('original.txt');
        expect($original->getFolder())->toBe('original-folder');
    });
});
