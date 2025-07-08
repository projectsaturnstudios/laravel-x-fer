<?php

namespace ProjectSaturnStudios\XFer\Providers;

use ProjectSaturnStudios\XFer\XFer;
use Illuminate\Support\ServiceProvider;
use ProjectSaturnStudios\XFer\Console\Commands\FileExodusCommand;

class XFerServiceProvider extends ServiceProvider
{
    protected array $config = [
        'file-transfers' => __DIR__ .'/../../config/file-transfers.php',
    ];

    protected array $commands = [
        FileExodusCommand::class,
    ];

    public function register(): void
    {
        $this->registerConfigs();
    }

    public function boot(): void
    {
        $this->publishConfigs();
        XFer::boot();
        $this->commands($this->commands);
    }

    protected function publishConfigs() : void
    {
        $this->publishes([
            $this->config['file-transfers'] => config_path('file-transfers.php'),
        ], 'xfer.config');
    }

    protected function registerConfigs() : void
    {
        foreach ($this->config as $key => $path) {
            $this->mergeConfigFrom($path, $key);
        }
    }
}
