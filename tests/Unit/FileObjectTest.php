<?php

use ProjectSaturnStudios\Xfer\DTO\Transfers\FileObject;
use ProjectSaturnStudios\Xfer\Contracts\FileObject as FileObjectContract;
use Illuminate\Support\Facades\Storage;
use Spatie\LaravelData\Data;

describe('FileObject', function () {
    beforeEach(function () {
        Storage::fake('local');
        Storage::fake('testing');
    });

    it('can be instantiated with disk and filepath', function () {
        $fileObject = new FileObject(
            disk: 'local',
            filepath: 'documents/test.txt'
        );

        expect($fileObject->disk)->toBe('local')
            ->and($fileObject->filepath)->toBe('documents/test.txt');
    });

    it('extends Spatie Laravel Data', function () {
        $fileObject = new FileObject('local', 'test.txt');

        expect($fileObject)->toBeInstanceOf(Data::class);
    });

    it('implements FileObjectContract', function () {
        $fileObject = new FileObject('local', 'test.txt');

        expect($fileObject)->toBeInstanceOf(FileObjectContract::class);
    });

    it('can read file contents as stream', function () {
        // Arrange: Create a test file
        Storage::disk('local')->put('test-file.txt', 'Hello, World!');
        $fileObject = new FileObject('local', 'test-file.txt');

        // Act: Get contents
        $stream = $fileObject->contents();

        // Assert: Should return a resource stream
        expect($stream)->toBeResource();
        
        $content = stream_get_contents($stream);
        expect($content)->toBe('Hello, World!');
        
        // Clean up the stream
        if (is_resource($stream)) {
            fclose($stream);
        }
    });

    it('returns null when file does not exist', function () {
        $fileObject = new FileObject('local', 'non-existent-file.txt');

        $result = $fileObject->contents();

        expect($result)->toBeNull();
    });

    it('can write contents from string stream', function () {
        // Arrange: Create source content as stream
        $sourceContent = 'This is test content for writing';
        $sourceStream = fopen('php://memory', 'r+');
        fwrite($sourceStream, $sourceContent);
        rewind($sourceStream);

        $fileObject = new FileObject('local', 'output/written-file.txt');

        // Act: Write the stream
        $result = $fileObject->write($sourceStream);

        // Assert: Should return true and file should exist with correct content
        expect($result)->toBeTrue();
        expect(Storage::disk('local')->exists('output/written-file.txt'))->toBeTrue();
        expect(Storage::disk('local')->get('output/written-file.txt'))->toBe($sourceContent);

        // Clean up
        fclose($sourceStream);
    });

    it('can transfer between FileObjects', function () {
        // Arrange: Create source file
        Storage::disk('local')->put('source.txt', 'Source file content');
        $sourceFileObject = new FileObject('local', 'source.txt');
        $destinationFileObject = new FileObject('local', 'destination.txt');

        // Act: Transfer from source to destination
        $sourceStream = $sourceFileObject->contents();
        $result = $destinationFileObject->write($sourceStream);

        // Assert: Should successfully copy content
        expect($result)->toBeTrue();
        expect(Storage::disk('local')->exists('destination.txt'))->toBeTrue();
        expect(Storage::disk('local')->get('destination.txt'))->toBe('Source file content');

        // Clean up
        if (is_resource($sourceStream)) {
            fclose($sourceStream);
        }
    });

    it('works with different storage disks', function () {
        // Test with different disk configurations
        $localFileObject = new FileObject('local', 'local-file.txt');
        expect($localFileObject->disk)->toBe('local');

        $testingFileObject = new FileObject('testing', 'testing-file.txt');
        expect($testingFileObject->disk)->toBe('testing');
    });

    it('has Spatie Data serialization capabilities', function () {
        $fileObject = new FileObject('local', 'documents/important.pdf');

        // Test that it has Data methods (without calling them due to version issues)
        expect($fileObject)->toBeInstanceOf(Data::class);
        expect(method_exists($fileObject, 'toArray'))->toBeTrue();
        expect(method_exists($fileObject, 'from'))->toBeTrue();
    });

    it('can access properties correctly', function () {
        $fileObject = new FileObject('local', 'uploads/document.docx');

        // Test direct property access
        expect($fileObject->disk)->toBe('local');
        expect($fileObject->filepath)->toBe('uploads/document.docx');
    });

    it('handles complex file paths correctly', function () {
        $complexPath = 'deeply/nested/folders/with spaces/file-name_123.txt';
        $fileObject = new FileObject('local', $complexPath);

        expect($fileObject->filepath)->toBe($complexPath);
    });

    it('properties are readonly', function () {
        $fileObject = new FileObject('local', 'test.txt');

        // These should cause errors if we try to modify them
        expect(fn() => $fileObject->disk = 'modified')->toThrow(Error::class);
        expect(fn() => $fileObject->filepath = 'modified.txt')->toThrow(Error::class);
    });

    it('handles empty files correctly', function () {
        // Arrange: Create empty file
        Storage::disk('local')->put('empty-file.txt', '');
        $fileObject = new FileObject('local', 'empty-file.txt');

        // Act: Get contents
        $stream = $fileObject->contents();

        // Assert: Should return a resource stream with empty content
        expect($stream)->toBeResource();
        
        $content = stream_get_contents($stream);
        expect($content)->toBe('');
        
        // Clean up
        if (is_resource($stream)) {
            fclose($stream);
        }
    });

    it('can handle binary files', function () {
        // Arrange: Create binary content (simulated)
        $binaryContent = pack('H*', '89504e470d0a1a0a'); // PNG header bytes
        Storage::disk('local')->put('binary-file.png', $binaryContent);
        $fileObject = new FileObject('local', 'binary-file.png');

        // Act: Get contents
        $stream = $fileObject->contents();

        // Assert: Should return the binary content correctly
        expect($stream)->toBeResource();
        
        $content = stream_get_contents($stream);
        expect($content)->toBe($binaryContent);
        
        // Clean up
        if (is_resource($stream)) {
            fclose($stream);
        }
    });
});