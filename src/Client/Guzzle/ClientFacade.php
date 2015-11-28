<?php

namespace CouchDbAdapter\Client\Guzzle\Facade;

use CouchDbAdapter\Client\ClientInterface;
use CouchDbAdapter\Client\Guzzle\GuzzleResponse;
use CouchDbAdapter\CouchDb\Document;
use GuzzleHttp\Client;

class ClientFacade implements ClientInterface
{
	/** @var Client */
	private $client;

	/**
	 * @param Client $client
	 */
	public function __construct(Client $client)
	{
		$this->client = $client;
	}

	/**
	 * @param string $method
	 * @param string $database
	 * @param Document|null $couchDbDocument
	 * @param string|null $authToken
	 *
	 * @return ResponseInterface
	 */
	public function request($method, $database, Document $couchDbDocument = null, $authToken = null)
	{
		$response = $this->client->request($method, $database);

		return new GuzzleResponse($response);
	}
}