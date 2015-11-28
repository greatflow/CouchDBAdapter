<?php

namespace CouchDbAdapter\Client\IntegrationTest;

use CouchDbAdapter\Client\Guzzle\GuzzleClient;
use PHPUnit_Framework_TestCase;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Exception\RequestException;

class GuzzleClientTest extends PHPUnit_Framework_TestCase
{
	/** @var GuzzleClient  */
	private $guzzleClient;

	public function setUp()
	{
		// Create a mock and queue two responses.
		$mock = new MockHandler([
			new Response(200, [], 'First Test Ok'),
			new Response(202, [], 'Second Test Ok'),
			new RequestException("Error Communicating with Server", new Request('GET', 'test'))
		]);
		$handler = HandlerStack::create($mock);

		$testCouchUri = 'http://127.0.0.1:5984';
		$client = new Client(['handler' => $handler, 'base_uri' => $testCouchUri]);
		$this->guzzleClient = new GuzzleClient($client, $testCouchUri);
	}

	public function test_invalid_method_throws_exception()
	{
		$method = 'SEND';
		$database = 'test';

		$this->setExpectedException('InvalidArgumentException');

		$this->guzzleClient->sendRequest($method, $database, [200]);
	}

	public function test_can_send_a_message_to_guzzle_client()
	{
		$method = 'POST';
		$database = 'test';

		$response = $this->guzzleClient->sendRequest($method, $database, ['200']);
		$this->assertEquals('First Test Ok', $response);

		$response = $this->guzzleClient->sendRequest($method, $database, ['202']);
		$this->assertEquals('Second Test Ok', $response);
	}
}