<?php

namespace CouchDbAdapter\Client;

use CouchDbAdapter\Client\Guzzle;
use CouchDbAdapter\CouchDb\Document;
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

	/**
	 * @param ClientInterface $client The client that will send requests to the CouchDB server
	 */
	public function __construct(ClientInterface $client)
	{
		$this->client = $client;
	}

	/**
	 * @param string $name
	 * @param array $args
	 * @return mixed
	 */
	public function __call($name, array $args)
	{
		array_unshift($args, strtoupper($name));
		return call_user_func_array(array($this, 'sendRequest'), $args);
	}

	/**
	 * @param string $method
	 * @param string $url
	 * @param array $expectedResponseCodes
	 * @param array $options
	 * @param Document|null $couchDbDocument
	 * @return mixed
	 */
	private function sendRequest($method, $url, array $expectedResponseCodes, $options = [], Document $couchDbDocument = null)
	{
		$this->isValidMethod($method);

		$response = $this->client->request($method, $url, $couchDbDocument, $options);

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