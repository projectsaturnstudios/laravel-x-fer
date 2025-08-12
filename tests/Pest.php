<?php

use Orchestra\Testbench\TestCase;
use ProjectSaturnStudios\Xfer\Providers\XferServiceProvider;

uses(TestCase::class)->in('Feature', 'Unit');

uses()->group('unit')->in('Unit');
uses()->group('feature')->in('Feature');

function getPackageProviders($app)
{
    return [
        XferServiceProvider::class,
    ];
}

function defineEnvironment($app)
{
    // Setup test environment
    $app['config']->set('filesystems.disks.test_local', [
        'driver' => 'local',
        'root' => storage_path('app/test_local'),
    ]);

    $app['config']->set('filesystems.disks.test_s3', [
        'driver' => 's3',
        'key' => 'test-key',
        'secret' => 'test-secret',
        'region' => 'us-east-1',
        'bucket' => 'test-bucket',
    ]);

    $app['config']->set('filesystems.disks.test_sftp', [
        'driver' => 'sftp',
        'host' => 'localhost',
        'username' => 'test',
        'password' => 'test',
        'root' => '/home/test',
    ]);
}
