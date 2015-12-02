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
	 * @param Document|nulll $document
	 * @return mixed
	 */
	private function sendRequest($method, $url, $expectedResponseCodes, $options, Document $document = null)
	{
		$this->isValidMethod($method);

		$response = $this->client->request($method, $url, $options, $document);

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
	 * @param int $expectedResponseCode
	 * @param $url
	 * @return mixed
	 * @throw CouchDbAdapter\Exceptions\CouchDbException
	 */
	private function handleResponse(ResponseInterface $response, $method, $expectedResponseCode, $url)
	{
		$response = $this->parseResponse($response);
		$statusCode = $response['statusCode'];

		if ($statusCode == $expectedResponseCode) {
			return json_decode($response['body'], true);
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