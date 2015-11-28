<?php

use CouchDbAdapter\Client\Guzzle\GuzzleClient;
use CouchDbAdapter\CouchDb\Server;
use GuzzleHttp\Client;

$guzzleClient = new GuzzleClient(new Client());

$couchDbServer = new Server($guzzleClient, '192.168.0.8');
$allDbs = $couchDbServer->listDbs();
