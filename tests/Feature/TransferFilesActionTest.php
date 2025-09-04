<?php

use ProjectSaturnStudios\Xfer\Actions\FileTransfer\TransferFilesAction;
use ProjectSaturnStudios\Xfer\DTO\Transfers\FileObject;
use Illuminate\Support\Facades\Storage;

describe('TransferFilesAction', function () {
    beforeEach(function () {
        // Set up fake storage disks for testing
        Storage::fake('local');
        Storage::fake('backup');
        Storage::fake('s3');
        
        $this->action = new TransferFilesAction();
    });

    it('can transfer a file from local to backup disk', function () {
        // Arrange: Create a test file on local disk
        $testContent = 'This is test file content for transfer testing.';
        Storage::disk('local')->put('documents/test-file.txt', $testContent);
        
        $source = new FileObject(
            disk: 'local',
            filepath: 'documents/test-file.txt'
        );
        
        $destination = new FileObject(
            disk: 'backup',
            filepath: 'backups/transferred-file.txt'
        );

        // Act: Transfer the file
        $result = $this->action->handle($source, $destination);

        // Assert: Verify transfer was successful
        expect($result)->toBeTrue()
            ->and(Storage::disk('backup')->exists('backups/transferred-file.txt'))->toBeTrue()
            ->and(Storage::disk('backup')->get('backups/transferred-file.txt'))->toBe($testContent);
    });

    it('can transfer a file to a different filename', function () {
        // Arrange: Create source file
        $originalContent = 'Original document content here.';
        Storage::disk('local')->put('uploads/original.pdf', $originalContent);
        
        $source = new FileObject('local', 'uploads/original.pdf');
        $destination = new FileObject('local', 'processed/renamed-document.pdf');

        // Act: Transfer with rename
        $result = $this->action->handle($source, $destination);

        // Assert: File exists with new name and same content
        expect($result)->toBeTrue()
            ->and(Storage::disk('local')->exists('processed/renamed-document.pdf'))->toBeTrue()
            ->and(Storage::disk('local')->get('processed/renamed-document.pdf'))->toBe($originalContent);
    });

    it('can transfer files between different storage systems', function () {
        // Arrange: Create file on local disk
        $fileContent = 'Multi-disk transfer test content.';
        Storage::disk('local')->put('temp/multi-disk-test.txt', $fileContent);
        
        $source = new FileObject('local', 'temp/multi-disk-test.txt');
        $destination = new FileObject('s3', 'cloud-storage/uploaded-file.txt');

        // Act: Transfer between different storage systems
        $result = $this->action->handle($source, $destination);

        // Assert: File transferred successfully to S3
        expect($result)->toBeTrue()
            ->and(Storage::disk('s3')->exists('cloud-storage/uploaded-file.txt'))->toBeTrue()
            ->and(Storage::disk('s3')->get('cloud-storage/uploaded-file.txt'))->toBe($fileContent);
    });

    it('can handle binary file transfers', function () {
        // Arrange: Create a binary file (simulated image content)
        $binaryContent = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8/5+hHgAHggJ/PchI7wAAAABJRU5ErkJggg==');
        Storage::disk('local')->put('images/test-image.png', $binaryContent);
        
        $source = new FileObject('local', 'images/test-image.png');
        $destination = new FileObject('backup', 'image-backups/copied-image.png');

        // Act: Transfer binary file
        $result = $this->action->handle($source, $destination);

        // Assert: Binary content preserved
        expect($result)->toBeTrue()
            ->and(Storage::disk('backup')->exists('image-backups/copied-image.png'))->toBeTrue()
            ->and(Storage::disk('backup')->get('image-backups/copied-image.png'))->toBe($binaryContent);
    });

    it('can transfer large text files', function () {
        // Arrange: Create a larger file
        $largeContent = str_repeat("This is line content for large file testing.\n", 1000);
        Storage::disk('local')->put('logs/large-log-file.txt', $largeContent);
        
        $source = new FileObject('local', 'logs/large-log-file.txt');
        $destination = new FileObject('backup', 'archived-logs/large-backup.txt');

        // Act: Transfer large file
        $result = $this->action->handle($source, $destination);

        // Assert: Large file transferred completely
        expect($result)->toBeTrue()
            ->and(Storage::disk('backup')->exists('archived-logs/large-backup.txt'))->toBeTrue()
            ->and(strlen(Storage::disk('backup')->get('archived-logs/large-backup.txt')))->toBe(strlen($largeContent));
    });

    it('handles transfer to nested directory structure', function () {
        // Arrange: Create source file
        $content = 'Nested directory test content.';
        Storage::disk('local')->put('source.txt', $content);
        
        $source = new FileObject('local', 'source.txt');
        $destination = new FileObject('backup', 'deep/nested/directory/structure/target.txt');

        // Act: Transfer to deeply nested path
        $result = $this->action->handle($source, $destination);

        // Assert: File created in nested structure
        expect($result)->toBeTrue()
            ->and(Storage::disk('backup')->exists('deep/nested/directory/structure/target.txt'))->toBeTrue()
            ->and(Storage::disk('backup')->get('deep/nested/directory/structure/target.txt'))->toBe($content);
    });

    it('can transfer files with special characters in filename', function () {
        // Arrange: Create file with special characters
        $content = 'Special filename test content.';
        $specialFilename = 'files/test-file-with-spaces & symbols (2024).txt';
        Storage::disk('local')->put($specialFilename, $content);
        
        $source = new FileObject('local', $specialFilename);
        $destination = new FileObject('backup', 'processed/cleaned-filename.txt');

        // Act: Transfer file with special characters
        $result = $this->action->handle($source, $destination);

        // Assert: Transfer successful despite special characters
        expect($result)->toBeTrue()
            ->and(Storage::disk('backup')->exists('processed/cleaned-filename.txt'))->toBeTrue()
            ->and(Storage::disk('backup')->get('processed/cleaned-filename.txt'))->toBe($content);
    });

    it('returns true when transfer is successful', function () {
        // Arrange: Simple transfer setup
        Storage::disk('local')->put('simple-test.txt', 'Simple content');
        
        $source = new FileObject('local', 'simple-test.txt');
        $destination = new FileObject('backup', 'simple-backup.txt');

        // Act & Assert: Method returns boolean true on success
        $result = $this->action->handle($source, $destination);
        
        expect($result)->toBeTrue()
            ->and($result)->toBeBool();
    });

    it('can be used in a fluent transfer chain', function () {
        // Arrange: Multiple files for chained transfers
        $content1 = 'First file content';
        $content2 = 'Second file content';
        
        Storage::disk('local')->put('file1.txt', $content1);
        Storage::disk('local')->put('file2.txt', $content2);
        
        $action = new TransferFilesAction();

        // Act: Chain multiple transfers
        $result1 = $action->handle(
            new FileObject('local', 'file1.txt'),
            new FileObject('backup', 'backup1.txt')
        );
        
        $result2 = $action->handle(
            new FileObject('local', 'file2.txt'),
            new FileObject('s3', 'cloud/backup2.txt')
        );

        // Assert: Both transfers successful
        expect($result1)->toBeTrue()
            ->and($result2)->toBeTrue()
            ->and(Storage::disk('backup')->exists('backup1.txt'))->toBeTrue()
            ->and(Storage::disk('s3')->exists('cloud/backup2.txt'))->toBeTrue();
    });
});
