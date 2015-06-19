ElfinderPhpConnector
====================

[ElFinder](https://github.com/Studio-42/elFinder) PHP backend, 5.4 compliant use [Client Server Api](https://github.com/Studio-42/elFinder/wiki/Client-Server-API-2.0)


### Basic Usage

```php
<?php
require __DIR__.'/../vendor/autoload.php';

use FDevs\ElfinderPhpConnector\Connector;
use FDevs\ElfinderPhpConnector\Driver\LocalDriver;

$connector = new Connector();

//add local driver
$local = new LocalDriver();
$local->setDriverOptions(['path' => 'uploads', 'rootDir' => 'path/to/web/dir/']);
$local->setAdditionalImages([
    'XL' => ['prefix' => 'XL', 'width' => 800, 'height' => 800],
    'M'  => ['prefix' => 'M', 'width' => 300, 'height' => 300]
]);
$connector->addDriver($local);


$src = $_SERVER["REQUEST_METHOD"] == 'POST' ? $_POST : $_GET;
$cmd = isset($src['cmd']) ? $src['cmd'] : '';

$response = $connector->run($cmd, $src);

echo json_encode($response);
```


### Add [Photatoes](https://github.com/4devs/Photatoes) drivers

```php
require __DIR__.'/../vendor/autoload.php';

use FDevs\ElfinderPhpConnector\Connector;
use FDevs\ElfinderPhpConnector\Driver\PhotatoesDriver;
use FDevs\Photatoes\Manager;
use FDevs\Photatoes\Adapter\YandexAdapter;

$connector = new Connector();

$manager = new Manager(new YandexAdapter('username'));
$photatoes = new PhotatoesDriver($manager);
$photatoes->setDriverOptions(['rootName' => 'yandex']);

$connector->addDriver($photatoes);

$src = $_SERVER["REQUEST_METHOD"] == 'POST' ? $_POST : $_GET;
$cmd = isset($src['cmd']) ? $src['cmd'] : '';

$response = $connector->run($cmd, $src);

echo json_encode($response);
```

### use your best driver

create driver

```php
<?php

namespace App\ElfinderPhpConnector\Driver;

use FDevs\ElfinderPhpConnector\Driver\DriverInterface;

class BestDriver implements DriverInterface
{
//...
}
```

add driver
```php
$connector->addDriver(new BestDriver());
```

if you need use advanced command implements interfaces

* `FDevs\ElfinderPhpConnector\Driver\Command\AdditionInterface` 
    * [netmount](https://github.com/Studio-42/elFinder/wiki/Client-Server-API-2.0#netmount)
* `FDevs\ElfinderPhpConnector\Driver\Command\ArchiveInterface` 
    * [archive](https://github.com/Studio-42/elFinder/wiki/Client-Server-API-2.0#archive) 
    * [extract](https://github.com/Studio-42/elFinder/wiki/Client-Server-API-2.0#extract)
* `FDevs\ElfinderPhpConnector\Driver\Command\BaseInterface`
    * [open](https://github.com/Studio-42/elFinder/wiki/Client-Server-API-2.0#open) 
    * [file](https://github.com/Studio-42/elFinder/wiki/Client-Server-API-2.0#file)
    * [tree](https://github.com/Studio-42/elFinder/wiki/Client-Server-API-2.0#tree)
    * [parents](https://github.com/Studio-42/elFinder/wiki/Client-Server-API-2.0#parents)
    * [ls](https://github.com/Studio-42/elFinder/wiki/Client-Server-API-2.0#ls)
    * [search](https://github.com/Studio-42/elFinder/wiki/Client-Server-API-2.0#search)
    * [size](https://github.com/Studio-42/elFinder/wiki/Client-Server-API-2.0#size)
* `FDevs\ElfinderPhpConnector\Driver\Command\FileInterface`
    * [mkdir](https://github.com/Studio-42/elFinder/wiki/Client-Server-API-2.0#mkdir) 
    * [upload](https://github.com/Studio-42/elFinder/wiki/Client-Server-API-2.0#upload)
    * [rm](https://github.com/Studio-42/elFinder/wiki/Client-Server-API-2.0#rm)
    * [rename](https://github.com/Studio-42/elFinder/wiki/Client-Server-API-2.0#rename)
    * [duplicate](https://github.com/Studio-42/elFinder/wiki/Client-Server-API-2.0#duplicate)
    * [paste](https://github.com/Studio-42/elFinder/wiki/Client-Server-API-2.0#paste)
    * [info](https://github.com/Studio-42/elFinder/wiki/Client-Server-API-2.0#info)
* `FDevs\ElfinderPhpConnector\Driver\Command\ImageInterface`
    * [tmb](https://github.com/Studio-42/elFinder/wiki/Client-Server-API-2.0#tmb) 
    * [resize](https://github.com/Studio-42/elFinder/wiki/Client-Server-API-2.0#resize)
    * [dim](https://github.com/Studio-42/elFinder/wiki/Client-Server-API-2.0#dim)
* `FDevs\ElfinderPhpConnector\Driver\Command\TextInterface`
    * [get](https://github.com/Studio-42/elFinder/wiki/Client-Server-API-2.0#get) 
    * [put](https://github.com/Studio-42/elFinder/wiki/Client-Server-API-2.0#put)
    * [mkfile](https://github.com/Studio-42/elFinder/wiki/Client-Server-API-2.0#mkfile)
