<?php

use ProjectSaturnStudios\Xfer\DTO\RecipientDetails;
use ProjectSaturnStudios\Xfer\Contracts\RecipientDetailsInterface;

describe('RecipientDetailsInterface', function () {
    test('implementation follows contract interface', function () {
        $details = new RecipientDetails();
        
        expect($details)->toBeInstanceOf(RecipientDetailsInterface::class);
    });

    test('contract methods return expected types', function () {
        $details = new RecipientDetails('s3', 'backup.csv', 'exports');
        
        expect($details->getDisk())->toBeString();
        expect($details->getPath())->toBeString();  
        expect($details->getFolder())->toBeString();
        expect($details->getFullPath())->toBeString();
    });

    test('fluent setters return new instances', function () {
        $original = new RecipientDetails();
        
        $withDisk = $original->disk('sftp');
        $withPath = $original->path('data.csv');
        $withFolder = $original->folder('backups');
        
        expect($withDisk)->not->toBe($original);
        expect($withPath)->not->toBe($original);
        expect($withFolder)->not->toBe($original);
        
        expect($withDisk)->toBeInstanceOf(RecipientDetailsInterface::class);
        expect($withPath)->toBeInstanceOf(RecipientDetailsInterface::class);
        expect($withFolder)->toBeInstanceOf(RecipientDetailsInterface::class);
    });

    test('full path construction matches source interface', function () {
        $details = new RecipientDetails('backup', 'report.pdf', 'monthly');
        
        expect($details->getFullPath())->toBe('monthly/report.pdf');
    });

    test('handles destination without folder', function () {
        $details = new RecipientDetails('local', 'simple.txt');
        
        expect($details->getFullPath())->toBe('simple.txt');
        expect($details->getFolder())->toBeNull();
    });

    test('immutability preserved across operations', function () {
        $original = new RecipientDetails('s3', 'original.txt', 'folder');
        
        $modified = $original
            ->disk('local')
            ->path('modified.txt')
            ->folder('new-folder');
        
        // Original unchanged
        expect($original->getDisk())->toBe('s3');
        expect($original->getPath())->toBe('original.txt');
        expect($original->getFolder())->toBe('folder');
        
        // Modified has new values
        expect($modified->getDisk())->toBe('local');
        expect($modified->getPath())->toBe('modified.txt');
        expect($modified->getFolder())->toBe('new-folder');
    });
});
