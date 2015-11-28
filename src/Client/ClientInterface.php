<?php

namespace CouchDbAdapter\Client;

use CouchDbAdapter\CouchDb\Document;

interface ClientInterface
{
	/**
	 * @param string $method
	 * @param string $url
	 * @param Document $couchDbDocument
	 * @param array $options
	 *
	 * @return ResponseInterface
	 */
	public function request($method, $url, Document $couchDbDocument = null, $options = []);
}