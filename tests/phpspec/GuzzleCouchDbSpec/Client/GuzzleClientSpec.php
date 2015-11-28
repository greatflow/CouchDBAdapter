<?php

namespace GuzzleCouchDbSpec\CouchDbAdapter\Client;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class GuzzleClientSpec extends ObjectBehavior
{
	public function it_is_initializable()
	{
		$this->shouldHaveType('CouchDbAdapter\Client\GuzzleClient');
	}

	public function let()
	{
		$this->beConstructedWith('http://192.168.0.8:5894');
	}

	public function it_should_be_able_to_send_a_request_to_the_guzzle_client()
	{
		$database = 'test';

		$this->sendRequest('PUT', $database, $doc, $authToken);
	}
}
