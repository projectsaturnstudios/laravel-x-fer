<?php

namespace ProjectSaturnStudios\Xfer\Providers;

use InvalidArgumentException;
use ProjectSaturnStudios\Xfer\Xfer;
use Illuminate\Container\Container;
use Illuminate\Support\ServiceProvider;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Contracts\Filesystem\Factory;
use ProjectSaturnStudios\Xfer\DTO\TransferRequest;
use ProjectSaturnStudios\Xfer\DTO\RecipientDetails;
use ProjectSaturnStudios\Xfer\Actions\TransferAction;
use ProjectSaturnStudios\Xfer\DTO\ReadableFileResource;
use ProjectSaturnStudios\Xfer\Builders\TransferRequestFactory;
use ProjectSaturnStudios\Xfer\Contracts\TransferActionInterface;
use ProjectSaturnStudios\Xfer\Contracts\RequestFactoryInterface;
use ProjectSaturnStudios\Xfer\Contracts\TransferRequestInterface;
use ProjectSaturnStudios\Xfer\Contracts\RecipientDetailsInterface;
use ProjectSaturnStudios\Xfer\Contracts\ReadableFileResourceInterface;
use ProjectSaturnStudios\Xfer\Contracts\FileTransferOrchestratorInterface;

class XferServiceProvider extends ServiceProvider
{
    protected array $config = [
        'file-transfers' => __DIR__ . '/../../config/file-transfers.php',
    ];

    protected array $commands = [
        //FileExodusCommand::class,
    ];

    public function register(): void
    {
        $this->registerConfigs();
    }

    public function boot(): void
    {
        $this->publishConfigs();
        $this->registerContainerObjects();
        $this->commands($this->commands);
    }

    public function registerContainerObjects(): void
    {
        $this->app->bind(RecipientDetailsInterface::class, fn() => new RecipientDetails());
        $this->app->bind(ReadableFileResourceInterface::class, fn() => new ReadableFileResource());
        $this->app->bind(TransferRequestInterface::class, function(Container $app, array $args) {
            if(($args[0] ?? false) && (!$args[0] instanceof ReadableFileResourceInterface)) throw new InvalidArgumentException('First argument must be an instance of ReadableFileResourceInterface');
            if(($args[1] ?? false) && (!$args[1] instanceof RecipientDetailsInterface)) throw new InvalidArgumentException('Second argument must be an instance of RecipientDetailsInterface');
            return new TransferRequest($args[0] ?? null, $args[1] ?? null);
        });

        $reference = resolve(TransferRequestInterface::class);
        $this->app->bind(RequestFactoryInterface::class, fn() => new TransferRequestFactory($reference::class));
        $this->app->bind(TransferActionInterface::class, fn() => new TransferAction(resolve(Factory::class), resolve(Dispatcher::class)));

        $this->app->bind(FileTransferOrchestratorInterface::class, function(Container $app, array $args) {
            if(($args[0] ?? false) && (!$args[0] instanceof TransferRequestInterface)) throw new InvalidArgumentException('First argument must be an instance of TransferRequestInterface');
            return new Xfer(
                request_factory: resolve(RequestFactoryInterface::class),
                action: resolve(TransferActionInterface::class),
                request: $args[0] ?? null
            );
        });
    }

    protected function publishConfigs() : void
    {
        $this->publishes([
            $this->config['file-transfers'] => config_path('file-transfers.php'),
        ], 'xfer');
    }

    protected function registerConfigs() : void
    {
        foreach ($this->config as $key => $path) {
            $this->mergeConfigFrom($path, $key);
        }
    }
}
