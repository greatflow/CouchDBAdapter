<?php

namespace CouchDbAdapter\Client;

use CouchDbAdapter\Client\Guzzle;
use InvalidArgumentException;

class Client
{
	const ALLOWED_HTTP_METHODS = [
		'GET',
		'HEAD',
		'POST',
		'PUT',
		'DELETE',
		'COPY'
	];

	protected $client;

	/** @var string */
	protected $dsn;

	/**
	 * @param ClientInterface $client The client that will send requests to the CouchDB server
	 * @param $dsn The location of the CouchDB server
	 */
	public function __construct(ClientInterface $client, $dsn)
	{
		$this->client = $client;
		$this->dsn = $dsn;
	}

	/**
	 * @param string $method
	 * @param string $database
	 * @param array $expectedResponseCodes
	 * @param null $couchDbDocument
	 * @param null $authToken
	 * @return mixed
	 */
	public function sendRequest($method, $database, array $expectedResponseCodes, $couchDbDocument = null, $authToken = null)
	{
		$this->isValidMethod($method);

		$response = $this->client->request($method, $database, $couchDbDocument, $authToken);

		$url = $this->dsn . '/' . $database;

		return $this->handleResponse($response, $method, $expectedResponseCodes, $url);
	}

	/**
	 * @param $method
	 * @throws InvalidArgumentException
	 */
	private function isValidMethod($method)
	{
		if (!in_array($method, self::ALLOWED_HTTP_METHODS)) {
			throw new InvalidArgumentException("Bad HTTP method: $method");
		}
	}

	/**
	 * @param ResponseInterface $response
	 * @param $method
	 * @param array $expectedResponseCodes
	 * @param $url
	 * @return mixed
	 * @throw CouchDbAdapter\Exceptions\CouchDbException
	 */
	private function handleResponse(ResponseInterface $response, $method, array $expectedResponseCodes, $url)
	{
		$response = $this->parseResponse($response);
		$statusCode = $response['statusCode'];

		if (in_array($statusCode, $expectedResponseCodes)) {
			return $response['body'];
		}

		throw ClientExceptionFactory::factory($response, $method, $url);
	}

	/**
	 * @param ResponseInterface $response
	 * @return array
	 */
	private function parseResponse(ResponseInterface $response)
	{
		$responseArray['statusCode'] = $response->getStatusCode();
		$responseArray['body'] = $response->getBody();
		$responseArray['headers'] = $response->getHeaders();

		return $responseArray;
	}
}