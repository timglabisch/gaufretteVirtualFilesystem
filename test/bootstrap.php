<?php

require_once __DIR__.'/../../../bin/vendor/symfony-class-loader/UniversalClassLoader.php';

use Symfony\Component\ClassLoader\UniversalClassLoader;

$loader = new UniversalClassLoader();
$loader->registerNamespaces(array(
    'Gaufrette'       => array(__DIR__.'/../../../src', __DIR__),
    'Pimcore'       => array(__DIR__.'/../../../src', __DIR__),
    'Ssh'             => __DIR__.'/../../../bin/vendor/php-ssh/src',
    'Doctrine\Common' => __DIR__.'/../../../bin/vendor/doctrine-common/lib',
    'Doctrine\DBAL'   => __DIR__.'/../../../bin/vendor/doctrine-dbal/lib',
));
$loader->registerPrefixes(array(
    'Dropbox_'        => __DIR__.'/../../../bin/vendor/dropbox-php/src',
));

$loader->register();

// AWS SDK needs a special autoloader
require_once __DIR__.'/../../../bin/vendor/aws-sdk/sdk.class.php';


/*
$adapter = new Gaufrette\Adapter\Local(__DIR__.'/data');
$configAdapter = new Gaufrette\Adapter\Local(__DIR__.'/etc');


$pimcoreFileSystem = new Pimcore\Filesystem($adapter);
$pimcoreFileSystem->mount('/etc', $configAdapter);

$file = new Gaufrette\File('newFile', $pimcoreFileSystem);
$file->setContent('Hello World2');

$file = new Gaufrette\File('/etc/newFile', $pimcoreFileSystem);
$file->setContent('ETC!');



var_dump($pimcoreFileSystem->listDirectory('/etc/'));
*/