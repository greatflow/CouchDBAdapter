<?php
namespace CouchDbAdapter\Client\Guzzle;

use CouchDbAdapter\Client\Client;
use CouchDbAdapter\Client\CouchDbClientExceptionFactory;
use GuzzleHttp\Cookie\CookieJar;
use GuzzleHttp\Cookie\SetCookie;

class GuzzleClient extends Client
{
	/** @var bool */
	private $debug = false;

	public function setDebugLevel($level)
	{
		if (! is_bool($level)) {
			throw new InvalidArgumentExceptio('The debug level must be a boolean value');
		}

		$this->debug = $level;
	}

	/**
	 * @param string $authToken
	 * @return CookieJar
	 */
	private function buildAuthCookie($authToken)
	{
		$cookie = new SetCookie();
		$cookie->setDomain('192.168.0.8');
		$cookie->setName('AuthSession');
		$cookie->setValue($authToken);

		return new CookieJar(false, [
			$cookie
		]);
	}

	/**
	 * @param array $request
	 * @return GuzzleResponse
	 */
	protected function sendRequestToClient(array $request)
	{
		$guzzleResponse = $this->client->request(
			$request['method'],
			$request['database'],
			$request['options']
		);

		return new GuzzleResponse($guzzleResponse);
	}

	/**
	 * @param string $method
	 * @param string $database
	 * @param $couchDbDocument
	 * @param string $authToken
	 * @return array
	 */
	protected function buildClientRequest($method, $database, $couchDbDocument, $authToken)
	{
		$options = [
			'json' => [
				'user' => 'test',
				'something' => 'this is a new test',
				'_rev' => '2-8798bb5f9fe20ec4e8b90b2f5d573081'
			]
		];

		if (isset($authToken)) {
			$options['headers'] = ['X-CouchDB-WWW-Authenticate' => 'Cookie'];
			$options['cookies'] = $this->buildAuthCookie($authToken);
		}

		if ($this->debug) {
			$options['debug'] = true;
		}

		$request = [
			'method' => $method,
			'database' => $database,
			'options' => $options
		];

		return $request;
	}
}