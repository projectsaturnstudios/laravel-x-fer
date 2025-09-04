<?php

namespace Tests;

use Orchestra\Testbench\TestCase as Orchestra;
use ProjectSaturnStudios\Xfer\Providers\XferServiceProvider;

abstract class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();

        // Additional setup can go here
    }

    protected function getPackageProviders($app): array
    {
        // Skip the service provider in tests since it requires event sourcing dependencies
        return [];
    }

    public function getEnvironmentSetUp($app): void
    {
        // Setup test environment configuration
        config()->set('database.default', 'testing');
        
        // Configure filesystems for testing
        config()->set('filesystems.disks.local', [
            'driver' => 'local',
            'root' => sys_get_temp_dir() . '/laravel-xfer-tests',
        ]);
        
        config()->set('filesystems.disks.s3', [
            'driver' => 's3',
            'key' => 'test-key',
            'secret' => 'test-secret',
            'region' => 'us-east-1',
            'bucket' => 'test-bucket',
        ]);
        
        config()->set('filesystems.disks.sftp', [
            'driver' => 'sftp',
            'host' => 'test-host',
            'username' => 'test-user',
            'password' => 'test-pass',
            'root' => '/test',
        ]);
    }
}
