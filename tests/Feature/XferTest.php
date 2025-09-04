<?php

use ProjectSaturnStudios\Xfer\Xfer;
use ProjectSaturnStudios\Xfer\DTO\Transfers\FileObject;
use ProjectSaturnStudios\Xfer\DTO\Transfers\TransferResultSuccess;
use ProjectSaturnStudios\Xfer\DTO\Transfers\TransferResultFailure;
use ProjectSaturnStudios\Xfer\Events\FileTransferStarted;
use ProjectSaturnStudios\Xfer\Events\FileTransferFinished;
use ProjectSaturnStudios\Xfer\Events\FileTransferFailed;
use ProjectSaturnStudios\Xfer\Exceptions\XFerException;
use ProjectSaturnStudios\Xfer\Events\Sourced\FileTransferLogged;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Event;

describe('Xfer Fluent API', function () {
    beforeEach(function () {
        Storage::fake('local');
        Storage::fake('backup');
        Storage::fake('s3');
        
        // Fake events to avoid database interactions
        Event::fake();
    });

    it('can create fluent transfer chain with from() and to()', function () {
        $xfer = new Xfer();
        
        $result = $xfer->from('local', 'documents', 'test.txt')
                      ->to('backup', 'archive', 'test-backup.txt');
        
        expect($result)->toBeInstanceOf(Xfer::class);
    });

    it('can perform ad-hoc transfer without logging', function () {
        // Arrange: Create test file
        Storage::disk('local')->put('documents/sample.txt', 'Test file content');
        
        // Act: Perform transfer
        $xfer = new Xfer();
        $result = $xfer->from('local', 'documents', 'sample.txt')
                      ->to('backup', 'archive', 'sample-backup.txt')
                      ->transfer();
        
        // Assert: Transfer successful
        expect($result)->toBeTrue();
        expect(Storage::disk('backup')->exists('archive/sample-backup.txt'))->toBeTrue();
        expect(Storage::disk('backup')->get('archive/sample-backup.txt'))->toBe('Test file content');
        
        // Assert: No events dispatched for ad-hoc transfer
        Event::assertNotDispatched(FileTransferStarted::class);
        Event::assertNotDispatched(FileTransferFinished::class);
        Event::assertNotDispatched(FileTransferFailed::class);
    });

    it('can perform logged transfer with event sourcing', function () {
        // Arrange: Create test file and enable logging
        Storage::disk('local')->put('documents/important.pdf', 'Important document content');
        
        // Act: Perform logged transfer
        $xfer = new Xfer(use_logging: true);
        $result = $xfer->from('local', 'documents', 'important.pdf')
                      ->to('s3', 'backups', 'important-backup.pdf')
                      ->transfer();
        
        // Assert: Transfer returns success result object
        expect($result)->toBeInstanceOf(TransferResultSuccess::class);
        expect($result->transfer_id)->not()->toBeEmpty();
        expect($result->source)->toBeInstanceOf(FileObject::class);
        expect($result->destination)->toBeInstanceOf(FileObject::class);
        expect($result->time_started)->not()->toBeNull();
        expect($result->time_finished)->not()->toBeNull();
        
        // Assert: File was actually transferred
        expect(Storage::disk('s3')->exists('backups/important-backup.pdf'))->toBeTrue();
        expect(Storage::disk('s3')->get('backups/important-backup.pdf'))->toBe('Important document content');
        
        // Assert: Events were dispatched for logging
        Event::assertDispatched(FileTransferStarted::class);
        Event::assertDispatched(FileTransferFinished::class);
        Event::assertDispatched(FileTransferLogged::class);
        Event::assertNotDispatched(FileTransferFailed::class);
    });

    it('handles logged transfer result types correctly', function () {
        // Arrange: Create successful transfer scenario
        Storage::disk('local')->put('documents/test.txt', 'Test content');
        
        // Act: Perform logged transfer
        $xfer = new Xfer(use_logging: true);
        $result = $xfer->from('local', 'documents', 'test.txt')
                      ->to('backup', 'archive', 'test.txt')
                      ->transfer();
        
        // Assert: Returns success result object (not boolean)
        expect($result)->toBeInstanceOf(TransferResultSuccess::class);
        expect($result->success())->toBeTrue();
        expect($result->message())->toBe('Transfer completed successfully');
        expect($result->exception())->toBeNull();
        
        // Assert: File was transferred
        expect(Storage::disk('backup')->exists('archive/test.txt'))->toBeTrue();
        
        // Assert: Events were dispatched for logging
        Event::assertDispatched(FileTransferStarted::class);
        Event::assertDispatched(FileTransferFinished::class);
        Event::assertDispatched(FileTransferLogged::class);
        Event::assertNotDispatched(FileTransferFailed::class);
    });

    it('throws exception when source is missing', function () {
        $xfer = new Xfer();
        
        expect(fn() => $xfer->to('backup', 'archive', 'test.txt')->transfer())
            ->toThrow(XFerException::class, 'The source file does not exist.');
    });

    it('throws exception when destination is missing', function () {
        $xfer = new Xfer();
        
        expect(fn() => $xfer->from('local', 'documents', 'test.txt')->transfer())
            ->toThrow(XFerException::class, 'The destination file does not exist.');
    });

    it('maintains immutability in fluent chain', function () {
        $xfer1 = new Xfer();
        $xfer2 = $xfer1->from('local', 'documents', 'test.txt');
        $xfer3 = $xfer2->to('backup', 'archive', 'test.txt');
        
        // Each step should return a new instance
        expect($xfer1)->not()->toBe($xfer2);
        expect($xfer2)->not()->toBe($xfer3);
        expect($xfer1)->not()->toBe($xfer3);
    });

    it('can handle complex file paths in fluent API', function () {
        // Arrange: Create nested file structure
        Storage::disk('local')->put('deep/nested/folder/structure/complex-file.json', '{"test": "data"}');
        
        // Act: Transfer with complex paths
        $xfer = new Xfer();
        $result = $xfer->from('local', 'deep/nested/folder', 'structure/complex-file.json')
                      ->to('backup', 'archived/deep', 'structure/complex-file.json')
                      ->transfer();
        
        // Assert: Transfer successful with complex paths
        expect($result)->toBeTrue();
        expect(Storage::disk('backup')->exists('archived/deep/structure/complex-file.json'))->toBeTrue();
        expect(Storage::disk('backup')->get('archived/deep/structure/complex-file.json'))->toBe('{"test": "data"}');
    });

    it('generates unique transfer IDs for logged transfers', function () {
        // Arrange: Create test files
        Storage::disk('local')->put('documents/file1.txt', 'Content 1');
        Storage::disk('local')->put('documents/file2.txt', 'Content 2');
        
        // Act: Perform two logged transfers
        $xfer = new Xfer(use_logging: true);
        
        $result1 = $xfer->from('local', 'documents', 'file1.txt')
                       ->to('backup', 'archive', 'file1.txt')
                       ->transfer();
        
        $result2 = $xfer->from('local', 'documents', 'file2.txt')
                       ->to('backup', 'archive', 'file2.txt')
                       ->transfer();
        
        // Assert: Different transfer IDs
        expect($result1)->toBeInstanceOf(TransferResultSuccess::class);
        expect($result2)->toBeInstanceOf(TransferResultSuccess::class);
        expect($result1->transfer_id)->not()->toBe($result2->transfer_id);
        
        // Assert: Both transfers completed
        expect(Storage::disk('backup')->exists('archive/file1.txt'))->toBeTrue();
        expect(Storage::disk('backup')->exists('archive/file2.txt'))->toBeTrue();
    });

    it('preserves logging setting through fluent chain', function () {
        // Arrange: Create test file
        Storage::disk('local')->put('documents/test.txt', 'Test content');
        
        // Act: Create logged transfer chain
        $xfer = new Xfer(use_logging: true);
        $result = $xfer->from('local', 'documents', 'test.txt')
                      ->to('backup', 'archive', 'test.txt')
                      ->transfer();
        
        // Assert: Logging was used (returns TransferResult object)
        expect($result)->toBeInstanceOf(TransferResultSuccess::class);
        
        // Assert: Events were dispatched
        Event::assertDispatched(FileTransferStarted::class);
        Event::assertDispatched(FileTransferFinished::class);
        Event::assertDispatched(FileTransferLogged::class);
    });
});
