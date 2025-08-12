<?php

use ProjectSaturnStudios\Xfer\DTO\TransferRequest;
use ProjectSaturnStudios\Xfer\DTO\ReadableFileResource;
use ProjectSaturnStudios\Xfer\DTO\RecipientDetails;
use ProjectSaturnStudios\Xfer\Contracts\TransferRequestInterface;

describe('TransferRequest', function () {
    test('can be instantiated with no parameters', function () {
        $request = new TransferRequest();
        
        expect($request->source)->toBeNull();
        expect($request->destination)->toBeNull();
        // expect($request->state)->toBe('uninitialized'); // Testing internal state
        expect($request->ready())->toBeFalse();
    });

    test('can be instantiated with source only', function () {
        $source = new ReadableFileResource('local', 'file.txt');
        $request = new TransferRequest($source);
        
        expect($request->source)->toBe($source);
        expect($request->destination)->toBeNull();
        // expect($request->state)->toBe('pending'); // Testing internal state
        expect($request->ready())->toBeFalse();
    });

    test('can be instantiated with destination only', function () {
        $destination = new RecipientDetails('s3', 'file.txt');
        $request = new TransferRequest(null, $destination);
        
        expect($request->source)->toBeNull();
        expect($request->destination)->toBe($destination);
        // expect($request->state)->toBe('pending'); // Testing internal state
        expect($request->ready())->toBeFalse();
    });

    test('can be instantiated with both source and destination', function () {
        $source = new ReadableFileResource('local', 'file.txt');
        $destination = new RecipientDetails('s3', 'file.txt');
        $request = new TransferRequest($source, $destination);
        
        expect($request->source)->toBe($source);
        expect($request->destination)->toBe($destination);
        // expect($request->state)->toBe('ready'); // Testing internal state
        expect($request->ready())->toBeTrue();
    });

    test('implements TransferRequestInterface', function () {
        $request = new TransferRequest();
        
        expect($request)->toBeInstanceOf(TransferRequestInterface::class);
    });

    test('extends Spatie Data', function () {
        $source = new ReadableFileResource('local', 'test.txt');
        $destination = new RecipientDetails('s3', 'test.txt');
        $request = new TransferRequest($source, $destination);
        
        expect($request)->toBeInstanceOf(\Spatie\LaravelData\Data::class);
    });

    test('source method returns new instance with source', function () {
        $original = new TransferRequest();
        $source = new ReadableFileResource('ftp', 'data.csv');
        
        $updated = $original->source($source);
        
        expect($updated)->not->toBe($original);
        expect($updated->source)->toBe($source);
        expect($updated->destination)->toBe($original->destination);
        expect($original->source)->toBeNull();
    });

    test('destination method returns new instance with destination', function () {
        $original = new TransferRequest();
        $destination = new RecipientDetails('sftp', 'backup.zip');
        
        $updated = $original->destination($destination);
        
        expect($updated)->not->toBe($original);
        expect($updated->destination)->toBe($destination);
        expect($updated->source)->toBe($original->source);
        expect($original->destination)->toBeNull();
    });

    test('getDestination returns destination', function () {
        $destination = new RecipientDetails('local', 'output.txt');
        $request = new TransferRequest(null, $destination);
        
        expect($request->getDestination())->toBe($destination);
    });

    test('getDestination returns null when no destination', function () {
        $request = new TransferRequest();
        
        expect($request->getDestination())->toBeNull();
    });

    test('getSourceStream returns resource when source exists', function () {
        Storage::fake('test');
        Storage::disk('test')->put('source.txt', 'test content');
        
        $source = new ReadableFileResource('test', 'source.txt');
        $request = new TransferRequest($source);
        
        $stream = $request->getSourceStream();
        
        expect($stream)->toBeResource();
        
        $content = stream_get_contents($stream);
        expect($content)->toBe('test content');
        
        fclose($stream);
    });

    test('getSourceStream throws exception when no source', function () {
        $request = new TransferRequest();
        
        expect(fn() => $request->getSourceStream())
            ->toThrow(DomainException::class, 'No source has been set for this transfer request');
    });

    test('getSourceStream throws exception when source returns invalid resource', function () {
        // Create a mock source that returns non-resource
        $source = Mockery::mock(ReadableFileResource::class);
        $source->shouldReceive('read')->andReturn('not-a-resource');
        
        $request = new TransferRequest($source);
        
        expect(fn() => $request->getSourceStream())
            ->toThrow(DomainException::class, 'The source did not return a valid stream resource');
    });


    test('preserves immutability', function () {
        $source = new ReadableFileResource('local', 'original.txt');
        $destination = new RecipientDetails('s3', 'original.txt');
        $original = new TransferRequest($source, $destination);
        
        $newSource = new ReadableFileResource('ftp', 'new.txt');
        $newDestination = new RecipientDetails('local', 'new.txt');
        
        // Creating new instances should not affect original
        $original->source($newSource);
        $original->destination($newDestination);
        
        expect($original->source)->toBe($source);
        expect($original->destination)->toBe($destination);
        expect($original->ready())->toBeTrue();
    });

    test('fluent chaining creates ready request', function () {
        $request = (new TransferRequest())
            ->source(new ReadableFileResource('backup', 'database.sql'))
            ->destination(new RecipientDetails('primary', 'database.sql'));
        
        expect($request->ready())->toBeTrue();
        // expect($request->state)->toBe('ready'); // Testing internal state
        expect($request->source->getDisk())->toBe('backup');
        expect($request->destination->getDisk())->toBe('primary');
    });

    test('handles complex transfer scenarios', function () {
        $source = new ReadableFileResource('s3-backup', 'quarterly-report.xlsx', 'reports/2024/q4');
        $destination = new RecipientDetails('sftp-client', 'quarterly-report.xlsx', 'deliveries/reports');
        
        $request = new TransferRequest($source, $destination);
        
        expect($request->ready())->toBeTrue();
        expect($request->source->getFullPath())->toBe('reports/2024/q4/quarterly-report.xlsx');
        expect($request->destination->getFullPath())->toBe('deliveries/reports/quarterly-report.xlsx');
    });

    test('supports replacing source in existing request', function () {
        $originalSource = new ReadableFileResource('local', 'old.txt');
        $destination = new RecipientDetails('s3', 'file.txt');
        $request = new TransferRequest($originalSource, $destination);
        
        $newSource = new ReadableFileResource('backup', 'new.txt');
        $updated = $request->source($newSource);
        
        expect($updated->source)->toBe($newSource);
        expect($updated->destination)->toBe($destination); // Preserved
        expect($updated->ready())->toBeTrue();
        expect($request->source)->toBe($originalSource); // Original unchanged
    });

    test('supports replacing destination in existing request', function () {
        $source = new ReadableFileResource('local', 'file.txt');
        $originalDestination = new RecipientDetails('s3', 'old.txt');
        $request = new TransferRequest($source, $originalDestination);
        
        $newDestination = new RecipientDetails('ftp', 'new.txt');
        $updated = $request->destination($newDestination);
        
        expect($updated->source)->toBe($source); // Preserved
        expect($updated->destination)->toBe($newDestination);
        expect($updated->ready())->toBeTrue();
        expect($request->destination)->toBe($originalDestination); // Original unchanged
    });
});
