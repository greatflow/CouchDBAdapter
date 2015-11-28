<?php

namespace CouchDbAdapter\Client;

use CouchDbAdapter\CouchDb\Document;

interface ClientInterface
{
	/**
	 * @param string $method
	 * @param string $database
	 * @param Document $couchDbDocument
	 * @param null|string $authToken
	 *
	 * @return ResponseInterface
	 */
	public function request($method, $database, Document $couchDbDocument = null, $authToken = null);
}