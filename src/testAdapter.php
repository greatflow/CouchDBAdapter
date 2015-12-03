<?php

$loader = require '../vendor/autoload.php';
$loader->add('AppName', __DIR__.'/../src/');

use CouchDbAdapter\Client\Client;
use CouchDbAdapter\Client\Guzzle\GuzzleClient;
use CouchDbAdapter\CouchDb\Document;
use CouchDbAdapter\CouchDb\Server;
use GuzzleHttp\Client as HttpClient;

$guzzleClient = new GuzzleClient(new HttpClient());
$guzzleClient->setDebugLevel(true);
$couchDbClient = new Client($guzzleClient);

$couchDbServer = new Server($couchDbClient, '127.0.0.1');
$couchDbServer->setAdminUser('admin', 'b0110ck5');
$adamAuthToken = $couchDbServer->getAuthToken('adam', 'test');

$couchDbServer = new Server($couchDbClient, '127.0.0.1');
$couchDbServer->setAuthCookieToken($adamAuthToken);
$availDatabases = $couchDbServer->listDbs();

$dbName = 'test';
if (! in_array($dbName, $availDatabases)) {
    $database = $couchDbServer->createDatabase($dbName);
} else {
    $database = $couchDbServer->getDatabase($dbName);
}


$document = $database->getDocumentById(123456);
$document->setUsingToken(['how awesome is this' =>'very']);
$database->saveDocument($document);
var_dump($document);
//$documents = $database->getAllDocuments(true);
//
//foreach ($documents as $document) {
//    var_dump($document);
//}

//$document = $database->copyDocument(124, 1234);

//$document->setNewProperty('Test worked :)');
//$database->saveDocument($document);


//$document = $database->getDocument(124);
//
//if ($document === false) {
//    $document = new Document(124);
//    $document->setSurname('Pye')
//             ->setAnotherField('Test');
//}
//
//$database->saveDocument($document);
//
//$database->deleteDocument($document);
//
//$document->setNewProperty('WTF');
//
//$database->saveDocument($document);
//
//var_dump($document);
