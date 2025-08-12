<?php

use ProjectSaturnStudios\Xfer\DTO\ReadableFileResource;
use ProjectSaturnStudios\Xfer\Contracts\ReadableFileResourceInterface;

describe('ReadableFileResourceInterface', function () {
    test('implementation follows contract interface', function () {
        $resource = new ReadableFileResource();
        
        expect($resource)->toBeInstanceOf(ReadableFileResourceInterface::class);
    });

    test('contract methods return expected types', function () {
        $resource = new ReadableFileResource('local', 'test.txt', 'uploads');
        
        expect($resource->getDisk())->toBeString();
        expect($resource->getPath())->toBeString();
        expect($resource->getFolder())->toBeString();
        expect($resource->getFullPath())->toBeString();
    });

    test('fluent setters return new instances', function () {
        $original = new ReadableFileResource();
        
        $withDisk = $original->disk('s3');
        $withPath = $original->path('file.txt');
        $withFolder = $original->folder('uploads');
        
        expect($withDisk)->not->toBe($original);
        expect($withPath)->not->toBe($original);
        expect($withFolder)->not->toBe($original);
        
        expect($withDisk)->toBeInstanceOf(ReadableFileResourceInterface::class);
        expect($withPath)->toBeInstanceOf(ReadableFileResourceInterface::class);
        expect($withFolder)->toBeInstanceOf(ReadableFileResourceInterface::class);
    });

    test('read method returns resource', function () {
        Storage::fake('test');
        Storage::disk('test')->put('test.txt', 'test content');
        
        $resource = new ReadableFileResource('test', 'test.txt');
        $stream = $resource->read();
        
        expect($stream)->toBeResource();
        fclose($stream);
    });

    test('full path construction works correctly', function () {
        $resource = new ReadableFileResource('local', 'file.txt', 'uploads');
        
        expect($resource->getFullPath())->toBe('uploads/file.txt');
    });

    test('handles file without folder', function () {
        $resource = new ReadableFileResource('local', 'file.txt');
        
        expect($resource->getFullPath())->toBe('file.txt');
        expect($resource->getFolder())->toBeNull();
    });
});
