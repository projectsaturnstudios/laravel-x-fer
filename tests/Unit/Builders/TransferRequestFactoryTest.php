<?php

use ProjectSaturnStudios\Xfer\Builders\TransferRequestFactory;
use ProjectSaturnStudios\Xfer\DTO\TransferRequest;
use ProjectSaturnStudios\Xfer\Contracts\RequestFactoryInterface;
use ProjectSaturnStudios\Xfer\Contracts\TransferRequestInterface;
use ProjectSaturnStudios\Xfer\Contracts\ReadableFileResourceInterface;
use ProjectSaturnStudios\Xfer\Contracts\RecipientDetailsInterface;

describe('TransferRequestFactory', function () {
    test('implements RequestFactoryInterface', function () {
        $factory = new TransferRequestFactory(TransferRequest::class);
        
        expect($factory)->toBeInstanceOf(RequestFactoryInterface::class);
    });

    test('is readonly class', function () {
        $reflection = new ReflectionClass(TransferRequestFactory::class);
        expect($reflection->isReadOnly())->toBeTrue();
    });

    test('can be instantiated with reference class', function () {
        $factory = new TransferRequestFactory(TransferRequest::class);
        
        expect($factory)->toBeInstanceOf(TransferRequestFactory::class);
    });

    test('make method creates instance of reference class', function () {
        // Mock the resolve function to return a TransferRequest
        app()->bind(TransferRequest::class, fn() => new TransferRequest());
        
        $factory = new TransferRequestFactory(TransferRequest::class);
        $request = $factory->make();
        
        expect($request)->toBeInstanceOf(TransferRequest::class);
        expect($request)->toBeInstanceOf(TransferRequestInterface::class);
    });

    test('make method throws exception for invalid reference class', function () {
        // Mock resolve to return something that doesn't implement the interface
        app()->bind('InvalidClass', fn() => new stdClass());
        
        $factory = new TransferRequestFactory('InvalidClass');
        
        expect(fn() => $factory->make())
            ->toThrow(DomainException::class, 'Transfer request class must implement TransferRequestInterface');
    });

    test('creates new instances on each call', function () {
        app()->bind(TransferRequest::class, fn() => new TransferRequest());
        
        $factory = new TransferRequestFactory(TransferRequest::class);
        $request1 = $factory->make();
        $request2 = $factory->make();
        
        expect($request1)->not->toBe($request2);
        expect($request1)->toBeInstanceOf(TransferRequestInterface::class);
        expect($request2)->toBeInstanceOf(TransferRequestInterface::class);
    });

    test('works with different TransferRequest implementations', function () {
        // Create a custom implementation for testing
        $customImplementation = new class implements TransferRequestInterface {
            public function ready(): bool { return false; }
            public function getDestination(): ?RecipientDetailsInterface { return null; }
            public function source(ReadableFileResourceInterface $source): static { return $this; }
            public function destination(RecipientDetailsInterface $destination): static { return $this; }
            public function getSourceStream() { return fopen('php://memory', 'r'); }
        };
        
        app()->bind('CustomTransferRequest', fn() => $customImplementation);
        
        $factory = new TransferRequestFactory('CustomTransferRequest');
        $request = $factory->make();
        
        expect($request)->toBe($customImplementation);
        expect($request)->toBeInstanceOf(TransferRequestInterface::class);
    });

    test('factory is deterministic with same reference class', function () {
        app()->bind(TransferRequest::class, fn() => new TransferRequest());
        
        $factory1 = new TransferRequestFactory(TransferRequest::class);
        $factory2 = new TransferRequestFactory(TransferRequest::class);
        
        $request1 = $factory1->make();
        $request2 = $factory2->make();
        
        // Should be same class type but different instances
        expect(get_class($request1))->toBe(get_class($request2));
        expect($request1)->not->toBe($request2);
    });

    test('handles container resolution failures gracefully', function () {
        // Don't bind anything, so resolution should fail
        $factory = new TransferRequestFactory('NonExistentClass');
        
        // This should throw an exception from the container, not our custom exception
        expect(fn() => $factory->make())
            ->toThrow(Exception::class);
    });

    test('validates interface compliance strictly', function () {
        // Create a class that looks like it might work but doesn't implement the interface
        $almostCorrect = new stdClass();
        
        app()->bind('AlmostCorrect', fn() => $almostCorrect);
        
        $factory = new TransferRequestFactory('AlmostCorrect');
        
        expect(fn() => $factory->make())
            ->toThrow(DomainException::class, 'Transfer request class must implement TransferRequestInterface');
    });

    test('factory works in Laravel service container context', function () {
        // Test that the factory works when resolved from the container
        app()->bind(RequestFactoryInterface::class, function() {
            return new TransferRequestFactory(TransferRequest::class);
        });
        
        app()->bind(TransferRequest::class, fn() => new TransferRequest());
        
        $factory = app(RequestFactoryInterface::class);
        $request = $factory->make();
        
        expect($factory)->toBeInstanceOf(TransferRequestFactory::class);
        expect($request)->toBeInstanceOf(TransferRequestInterface::class);
    });

    test('reference class string is preserved', function () {
        $className = TransferRequest::class;
        $factory = new TransferRequestFactory($className);
        
        // Use reflection to check the protected property
        $reflection = new ReflectionClass($factory);
        $property = $reflection->getProperty('reference_class');
        $property->setAccessible(true);
        
        expect($property->getValue($factory))->toBe($className);
    });
});
