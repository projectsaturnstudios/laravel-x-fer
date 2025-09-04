
1. One-off Transfer - true or false
```php
use ProjectSaturnStudios\Xfer\Support\Facades\CopyFile;


$copied = CopyFile::from('sftp', 'folder', 'doc.csv')
    ->to('local', 'folder', 'saved-doc.csv')
    ->transfer();
```

2. Transfer with Logging
```php
$results = CopyFile::from('sftp', 'folder', 'doc.csv')
    ->to('local', 'folder', 'saved-doc.csv')
    ->withLogging()
    ->transfer();
```

3. Transfer Multiple Files
```php
use ProjectSaturnStudios\XFer\DTO\Transfers\FileObject;

$batch = BatchTransfer::driver($driver);
foreach($transfers as $transfer)
{
    $batch = $batch->addTransfer($transfer['source'], $transfer['destination'])
}
$results = $batch->dispatch();
```

4. Transfer Multiple Files with Logging
```php
use ProjectSaturnStudios\XFer\DTO\Transfers\FileObject;

$batch = BatchTransfer::driver($driver);
foreach($transfers as $transfer)
{
    $batch = $batch->addTransfer($transfer['source'], $transfer['destination'])
}
$results = $batch->withLogging()->dispatch();
```


Batch Transfer Drivers
1. Concurrency
2. Bus Batch
3. Bus Chain
4. Sync



