<?php

use ProjectSaturnStudios\Xfer\DTO\TransferRequest;
use ProjectSaturnStudios\Xfer\DTO\ReadableFileResource;
use ProjectSaturnStudios\Xfer\DTO\RecipientDetails;
use ProjectSaturnStudios\Xfer\Contracts\TransferRequestInterface;

describe('TransferRequestInterface', function () {
    test('implementation follows contract interface', function () {
        $request = new TransferRequest();
        
        expect($request)->toBeInstanceOf(TransferRequestInterface::class);
    });

    test('ready method returns boolean', function () {
        $emptyRequest = new TransferRequest();
        $fullRequest = new TransferRequest(
            new ReadableFileResource('local', 'test.txt'),
            new RecipientDetails('s3', 'test.txt')
        );
        
        expect($emptyRequest->ready())->toBeBool();
        expect($fullRequest->ready())->toBeBool();
        expect($fullRequest->ready())->toBeTrue();
        expect($emptyRequest->ready())->toBeFalse();
    });

    test('getDestination returns correct type', function () {
        $destination = new RecipientDetails('s3', 'file.txt');
        $request = new TransferRequest(null, $destination);
        
        expect($request->getDestination())->toBe($destination);
        
        $emptyRequest = new TransferRequest();
        expect($emptyRequest->getDestination())->toBeNull();
    });

    test('source setter returns new instance', function () {
        $original = new TransferRequest();
        $source = new ReadableFileResource('local', 'source.txt');
        
        $withSource = $original->source($source);
        
        expect($withSource)->not->toBe($original);
        expect($withSource)->toBeInstanceOf(TransferRequestInterface::class);
    });

    test('destination setter returns new instance', function () {
        $original = new TransferRequest();
        $destination = new RecipientDetails('s3', 'dest.txt');
        
        $withDestination = $original->destination($destination);
        
        expect($withDestination)->not->toBe($original);
        expect($withDestination)->toBeInstanceOf(TransferRequestInterface::class);
    });

    test('getSourceStream returns resource when ready', function () {
        Storage::fake('test');
        Storage::disk('test')->put('source.txt', 'test content');
        
        $source = new ReadableFileResource('test', 'source.txt');
        $destination = new RecipientDetails('test', 'dest.txt');
        $request = new TransferRequest($source, $destination);
        
        $stream = $request->getSourceStream();
        
        expect($stream)->toBeResource();
        fclose($stream);
    });

    test('getSourceStream throws exception when no source', function () {
        $request = new TransferRequest();
        
        expect(fn() => $request->getSourceStream())
            ->toThrow(DomainException::class, 'No source has been set');
    });

    test('state transitions correctly', function () {
        $source = new ReadableFileResource('local', 'file.txt');
        $destination = new RecipientDetails('s3', 'file.txt');
        
        // Uninitialized
        $empty = new TransferRequest();
        expect($empty->ready())->toBeFalse();
        
        // Pending with source only
        $withSource = $empty->source($source);
        expect($withSource->ready())->toBeFalse();
        
        // Pending with destination only  
        $withDest = $empty->destination($destination);
        expect($withDest->ready())->toBeFalse();
        
        // Ready with both
        $ready = $withSource->destination($destination);
        expect($ready->ready())->toBeTrue();
    });
});
