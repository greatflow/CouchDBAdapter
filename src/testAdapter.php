<?php

$loader = require '../vendor/autoload.php';
$loader->add('AppName', __DIR__.'/../src/');

use CouchDbAdapter\Client\Client;
use CouchDbAdapter\Client\Guzzle\GuzzleClient;
use CouchDbAdapter\CouchDb\Server;
use GuzzleHttp\Client as HttpClient;

$guzzleClient = new GuzzleClient(new HttpClient());
//$guzzleClient->setDebugLevel(true);
$couchDbClient = new Client($guzzleClient);

$couchDbServer = new Server($couchDbClient, '127.0.0.1');
$allDbs = $couchDbServer->createDb('test');
var_dump($allDbs);
