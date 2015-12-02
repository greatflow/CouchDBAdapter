<?php

namespace CouchDbAdapter\Client;

use CouchDbAdapter\CouchDb\Document;

interface ClientInterface
{
	/**
	 * @param string $method
	 * @param string $url
     * @param array $options
	 * @param Document $couchDbDocument
	 *
	 * @return ResponseInterface
	 */
	public function request($method, $url, $options, Document $couchDbDocument = null);
}