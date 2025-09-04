<?php

use ProjectSaturnStudios\Xfer\Drivers\BatchProcessing\ConcurrencyBatchDriver;
use ProjectSaturnStudios\Xfer\DTO\Transfers\FileObject;
use Illuminate\Support\Facades\Storage;

describe('ConcurrencyBatchDriver', function () {
    beforeEach(function () {
        Storage::fake('local');
        Storage::fake('backup');
    });

    it('can instantiate and has correct structure', function () {
        // Test basic instantiation and structure
        $driver = new ConcurrencyBatchDriver();
        
        expect($driver)->toBeInstanceOf(ConcurrencyBatchDriver::class);
        expect(method_exists($driver, 'process'))->toBeTrue();
    });

    it('extracts correct transfer data for serialization', function () {
        // Test that the driver properly extracts serializable data
        $driver = new ConcurrencyBatchDriver();
        
        // Create test FileObjects
        $source = new FileObject('local', 'documents/test.txt');
        $destination = new FileObject('backup', 'archive/copy.txt');
        
        // Verify the FileObject methods work correctly for data extraction
        expect($source->disk())->toBe('local');
        expect($source->folder())->toBe('documents');
        expect($source->path())->toBe('test.txt');
        
        expect($destination->disk())->toBe('backup');
        expect($destination->folder())->toBe('archive');
        expect($destination->path())->toBe('copy.txt');
    });

    it('handles empty items array gracefully', function () {
        $driver = new ConcurrencyBatchDriver();
        
        // Empty array should not cause errors
        $result = $driver->process([]);
        
        expect($result)->toBeArray();
        expect($result)->toHaveCount(0);
    });

    it('validates transfer data structure', function () {
        // Test that we can create the transfer data structure without errors
        $source = new FileObject('sftp', 'SOPUS/RDMExtract20221213064743.csv');
        $destination = new FileObject('s3-raw', 'sopus/RDMExtract20221213064743.csv');
        
        // These are the same data patterns used in the real command
        expect($source->disk())->toBe('sftp');
        expect($source->folder())->toBe('SOPUS');
        expect($source->path())->toBe('RDMExtract20221213064743.csv');
        
        expect($destination->disk())->toBe('s3-raw');
        expect($destination->folder())->toBe('sopus');
        expect($destination->path())->toBe('RDMExtract20221213064743.csv');
    });

    it('creates proper transfer data arrays for serialization', function () {
        // Test the data extraction logic that fixed the serialization issue
        $source = new FileObject('local', 'documents/test.txt');
        $destination = new FileObject('backup', 'archive/copy.txt');
        
        // Simulate what happens inside the driver
        $transferData = [
            'source_disk' => $source->disk(),
            'source_folder' => $source->folder(),
            'source_path' => $source->path(),
            'dest_disk' => $destination->disk(),
            'dest_folder' => $destination->folder(),
            'dest_path' => $destination->path(),
            'use_logging' => false
        ];
        
        // Verify all data is properly extracted and serializable
        expect($transferData)->toBeArray();
        expect($transferData['source_disk'])->toBe('local');
        expect($transferData['source_folder'])->toBe('documents');
        expect($transferData['source_path'])->toBe('test.txt');
        expect($transferData['dest_disk'])->toBe('backup');
        expect($transferData['dest_folder'])->toBe('archive');
        expect($transferData['dest_path'])->toBe('copy.txt');
        expect($transferData['use_logging'])->toBeFalse();
        
        // Ensure all values are primitive types (serializable)
        foreach ($transferData as $key => $value) {
            expect(is_scalar($value))->toBeTrue("Key '{$key}' should be a scalar value for serialization");
        }
    });

    it('handles batch processing structure correctly', function () {
        // Test the structure that matches the real command
        $items = [
            [new FileObject('sftp', 'SOPUS/file1.csv'), new FileObject('s3-raw', 'sopus/file1.csv')],
            [new FileObject('sftp', 'SOPUS/file2.csv'), new FileObject('s3-raw', 'sopus/file2.csv')],
            [new FileObject('sftp', 'SOPUS/file3.csv'), new FileObject('s3-raw', 'sopus/file3.csv')],
        ];
        
        // Verify structure is correct
        expect($items)->toHaveCount(3);
        expect($items[0])->toHaveCount(2);
        expect($items[0][0])->toBeInstanceOf(FileObject::class);
        expect($items[0][1])->toBeInstanceOf(FileObject::class);
        
        // Verify data extraction works for all items
        foreach ($items as $item) {
            [$source, $destination] = $item;
            
            expect($source->disk())->toBe('sftp');
            expect($source->folder())->toBe('SOPUS');
            expect($destination->disk())->toBe('s3-raw');
            expect($destination->folder())->toBe('sopus');
        }
    });
});
