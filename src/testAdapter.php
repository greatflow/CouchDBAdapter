<?php

$loader = require '../vendor/autoload.php';
$loader->add('AppName', __DIR__.'/../src/');

use CouchDbAdapter\Client\Client;
use CouchDbAdapter\Client\Guzzle\GuzzleClient;
use CouchDbAdapter\CouchDb\Document;
use CouchDbAdapter\CouchDb\Server;
use GuzzleHttp\Client as HttpClient;

$guzzleClient = new GuzzleClient(new HttpClient());
//$guzzleClient->setDebugLevel(true);
$couchDbClient = new Client($guzzleClient);

$couchDbServer = new Server($couchDbClient, '127.0.0.1');
$couchDbServer->setAdminUser('admin', 'b0110ck5');
//

$availDatabases = $couchDbServer->listDbs();
$dbName = 'test';
if (! in_array($dbName, $availDatabases)) {
    $database = $couchDbServer->createDatabase($dbName);
} else {
    $database = $couchDbServer->getDatabase($dbName);
}


$document = new Document(124);
$document->setSurname('Pye')
         ->setAnotherField('Test');


$database->saveDocument($document);

$database->deleteDocument($document);

$document->setNewProperty('I\'m back motherfuckers');

$database->saveDocument($document);

var_dump($document);
