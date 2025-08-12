<?php

use ProjectSaturnStudios\Xfer\Xfer;
use ProjectSaturnStudios\Xfer\Actions\TransferAction;
use ProjectSaturnStudios\Xfer\Builders\TransferRequestFactory;
use ProjectSaturnStudios\Xfer\DTO\ReadableFileResource;
use ProjectSaturnStudios\Xfer\DTO\RecipientDetails;
use ProjectSaturnStudios\Xfer\DTO\TransferRequest;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Event;

describe('Simple Functional Tests', function () {
    beforeEach(function () {
        // Set up real Laravel storage
        Storage::fake('test_source');
        Storage::fake('test_destination');
        
        // Create a real test file
        Storage::disk('test_source')->put('hello.txt', 'Hello World Content!');
        
        Event::fake();
    });

    test('can create orchestrator and check readiness', function () {
        $factory = new TransferRequestFactory(TransferRequest::class);
        $action = new TransferAction(Storage::getFacadeRoot(), Event::getFacadeRoot());
        $orchestrator = new Xfer($factory, $action);

        expect($orchestrator->ready())->toBeFalse();

        $readyOrchestrator = $orchestrator
            ->from(new ReadableFileResource('test_source', 'hello.txt'))
            ->to(new RecipientDetails('test_destination', 'output.txt'));

        expect($readyOrchestrator->ready())->toBeTrue();
    });

    test('ReadableFileResource can read from storage', function () {
        $resource = new ReadableFileResource('test_source', 'hello.txt');
        
        $stream = $resource->read();
        expect(is_resource($stream))->toBeTrue();
        
        $content = stream_get_contents($stream);
        expect($content)->toBe('Hello World Content!');
        
        fclose($stream);
    });

    test('TransferRequest can get source stream', function () {
        $source = new ReadableFileResource('test_source', 'hello.txt');
        $destination = new RecipientDetails('test_destination', 'output.txt');
        $request = new TransferRequest($source, $destination);

        expect($request->ready())->toBeTrue();
        
        $stream = $request->getSourceStream();
        expect(is_resource($stream))->toBeTrue();
        
        $content = stream_get_contents($stream);
        expect($content)->toBe('Hello World Content!');
        
        fclose($stream);
    });

    test('can manually execute transfer action', function () {
        $source = new ReadableFileResource('test_source', 'hello.txt');
        $destination = new RecipientDetails('test_destination', 'output.txt');
        $request = new TransferRequest($source, $destination);
        
        $action = new TransferAction(Storage::getFacadeRoot(), Event::getFacadeRoot());
        
        $result = $action->transfer($request);
        
        // Debug what we got
        dump([
            'result_class' => get_class($result),
            'success' => $result->success(),
            'message' => $result->message(),
            'exception' => $result->exception() ? $result->exception()->getMessage() : null,
            'file_exists' => Storage::disk('test_destination')->exists('output.txt'),
            'file_content' => Storage::disk('test_destination')->exists('output.txt') 
                ? Storage::disk('test_destination')->get('output.txt') 
                : null
        ]);
        
        expect($result->success())->toBeTrue();
    });
});
