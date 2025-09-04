<?php

namespace ProjectSaturnStudios\Xfer\Providers;

use ProjectSaturnStudios\LaravelDesignPatterns\Providers\BaseServiceProvider;
use ProjectSaturnStudios\Xfer\Managers\BatchProcessingManager;
use ProjectSaturnStudios\Xfer\Projectors\FileTransferLogProjector;
use Spatie\EventSourcing\Exceptions\InvalidEventHandler;
use Spatie\EventSourcing\Projectionist;

class XFerServiceProvider extends BaseServiceProvider
{
    protected array $config = [
        'file-transfers' => __DIR__ . '/../../config/file-transfers.php',
    ];

    protected array $publishable_config = [
        ['key' => 'file-transfers', 'file_path' => __DIR__ . '/../../config/file-transfers.php', 'groups' => ['file-transfers']],
    ];

    protected array $commands = [];

    protected array $bootables = [
        BatchProcessingManager::class
    ];

    protected array $migrations = [
        'file_transfer_logs'
    ];

    /**
     * @return void
     * @throws InvalidEventHandler
     */
    protected function mainBooted(): void
    {
        $this->publishMigrations();
        app(Projectionist::class)->addProjector(FileTransferLogProjector::class);
    }

    public function publishMigrations() : void
    {
        foreach ($this->migrations as $module_table_name) {
            $modules = collect(scandir(base_path('database/migrations')))->filter(function($item) use($module_table_name) {
                return str_contains($item, "create_{$module_table_name}_table");
            })->toArray();

            if(empty($modules))
            {
                $timestamp = date('Y_m_d_His', time());
                $stub = __DIR__."/../../database/migrations/create_{$module_table_name}_table.php";
                $target = $this->app->databasePath().'/migrations/'.$timestamp."_create_{$module_table_name}_table.php";

                $this->publishes([$stub => $target], "xfer.migrations.all");
                $this->publishes([$stub => $target], "xfer.migrations.{$module_table_name}");
            }
        }
    }

}
