<?php

use ProjectSaturnStudios\Xfer\DTO\RecipientDetails;
use ProjectSaturnStudios\Xfer\Contracts\RecipientDetailsInterface;

describe('RecipientDetails', function () {
    test('can be instantiated with all parameters', function () {
        $details = new RecipientDetails('local', 'backup.zip', 'backups');
        
        expect($details->getDisk())->toBe('local');
        expect($details->getPath())->toBe('backup.zip');
        expect($details->getFolder())->toBe('backups');
    });

    test('can be instantiated with minimal parameters', function () {
        $details = new RecipientDetails();
        
        expect($details->getDisk())->toBeNull();
        expect($details->getPath())->toBeNull();
        expect($details->getFolder())->toBeNull();
    });

    test('disk method returns new instance with updated disk', function () {
        $original = new RecipientDetails();
        $updated = $original->disk('ftp');
        
        expect($updated)->not->toBe($original);
        expect($updated->getDisk())->toBe('ftp');
        expect($original->getDisk())->toBeNull();
    });

    test('path method returns new instance with updated path', function () {
        $original = new RecipientDetails('s3');
        $updated = $original->path('archive.tar.gz');
        
        expect($updated)->not->toBe($original);
        expect($updated->getPath())->toBe('archive.tar.gz');
        expect($updated->getDisk())->toBe('s3'); // Preserved
        expect($original->getPath())->toBeNull();
    });

    test('folder method returns new instance with updated folder', function () {
        $original = new RecipientDetails('local', 'file.txt');
        $updated = $original->folder('archives');
        
        expect($updated)->not->toBe($original);
        expect($updated->getFolder())->toBe('archives');
        expect($updated->getDisk())->toBe('local'); // Preserved
        expect($updated->getPath())->toBe('file.txt'); // Preserved
    });

    test('getFullPath constructs correct path with folder', function () {
        $details = new RecipientDetails('s3', 'data.json', 'api-backups');
        
        expect($details->getFullPath())->toBe('api-backups/data.json');
    });

    test('getFullPath returns just filename without folder', function () {
        $details = new RecipientDetails('local', 'output.csv');
        
        expect($details->getFullPath())->toBe('output.csv');
    });

    test('getFullPath returns null when no path', function () {
        $details = new RecipientDetails('s3', null, 'folder');
        
        expect($details->getFullPath())->toBeNull();
    });

    test('implements RecipientDetailsInterface', function () {
        $details = new RecipientDetails();
        
        expect($details)->toBeInstanceOf(RecipientDetailsInterface::class);
    });

    test('extends Spatie Data', function () {
        $details = new RecipientDetails('sftp', 'export.xml', 'transfers');
        
        expect($details)->toBeInstanceOf(\Spatie\LaravelData\Data::class);
    });

    test('fluent chaining works correctly', function () {
        $details = (new RecipientDetails())
            ->disk('remote-sftp')
            ->folder('client-deliveries')
            ->path('final-report.pdf');
        
        expect($details->getDisk())->toBe('remote-sftp');
        expect($details->getFolder())->toBe('client-deliveries');
        expect($details->getPath())->toBe('final-report.pdf');
        expect($details->getFullPath())->toBe('client-deliveries/final-report.pdf');
    });

    test('handles nested folder structures', function () {
        $details = new RecipientDetails(
            'enterprise-storage', 
            'invoice-2024-001.pdf', 
            'clients/acme-corp/invoices/2024'
        );
        
        expect($details->getFullPath())->toBe('clients/acme-corp/invoices/2024/invoice-2024-001.pdf');
    });

    test('immutability is preserved across operations', function () {
        $original = new RecipientDetails('original-disk', 'original.txt', 'original-folder');
        
        // Multiple operations that should not affect original
        $original->disk('modified-disk');
        $original->path('modified.txt');
        $original->folder('modified-folder');
        
        // Original should be unchanged
        expect($original->getDisk())->toBe('original-disk');
        expect($original->getPath())->toBe('original.txt');
        expect($original->getFolder())->toBe('original-folder');
    });

    test('handles empty folder gracefully', function () {
        $details = new RecipientDetails('local', 'file.txt', '');
        
        expect($details->getFullPath())->toBe('file.txt');
    });

    test('different instances with same values have same properties', function () {
        $details1 = new RecipientDetails('s3', 'file.txt', 'folder');
        $details2 = new RecipientDetails('s3', 'file.txt', 'folder');
        
        expect($details1->getDisk())->toBe($details2->getDisk());
        expect($details1->getPath())->toBe($details2->getPath());
        expect($details1->getFolder())->toBe($details2->getFolder());
        expect($details1->getFullPath())->toBe($details2->getFullPath());
    });

    test('supports various file types and extensions', function () {
        $fileTypes = [
            'document.pdf',
            'spreadsheet.xlsx',
            'archive.tar.gz',
            'image.jpg',
            'script.sh',
            'data.json',
            'backup.sql'
        ];

        foreach ($fileTypes as $fileName) {
            $details = new RecipientDetails('storage', $fileName, 'files');
            
            expect($details->getPath())->toBe($fileName);
            expect($details->getFullPath())->toBe("files/{$fileName}");
        }
    });
});
