<?php

namespace CouchDbAdapter\Client;

use CouchDbAdapter\CouchDb\Document;

interface ClientInterface
{
	/**
	 * @param string $method
	 * @param string $url
     * @param array $options
	 * @param Document $document
	 *
	 * @return ResponseInterface
	 */
	public function request($method, $url, array $options, Document $document);
}