<?php
namespace CouchDbAdapter\Client\Guzzle;

use CouchDbAdapter\Client\ClientInterface;
use CouchDbAdapter\Client\CouchDbClientExceptionFactory;
use CouchDbAdapter\CouchDb\Document;
use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;
use GuzzleHttp\Cookie\SetCookie;
use InvalidArgumentException;

class GuzzleClient implements ClientInterface
{
	/** @var Client */
	private $client;

	/** @var bool */
	private $debug = false;

	/**
	 * @param Client $client
	 */
	public function __construct(Client $client)
	{
		$this->client = $client;
	}

	/**
	 * @param bool $level
	 * @throws InvalidArgumentException
	 */
	public function setDebugLevel($level)
	{
		if (! is_bool($level)) {
			throw new InvalidArgumentException('The debug level must be a boolean value');
		}

		$this->debug = $level;
	}

	/**
	 * @param array $authToken
	 * @return CookieJar
	 */
	private function buildAuthCookie(array $authToken)
	{
		$cookie = new SetCookie();
		$cookie->setDomain($authToken['cookieDomain']);
		$cookie->setName($authToken['cookieName']);
		$cookie->setValue($authToken['cookieValue']);

		return new CookieJar(false, [
			$cookie
		]);
	}

	/**
	 * @param string $method
	 * @param string $url
	 * @param Document|null $couchDbDocument
	 * @param array $options
	 * @return GuzzleResponse
	 */
	public function request($method, $url, $options, Document $couchDbDocument = null)
	{
		$options = $this->buildRequestOptions($options, $couchDbDocument);

		$guzzleResponse = $this->client->request(
			$method,
			$url,
			$options
		);

		return new GuzzleResponse($guzzleResponse);
	}

	/**
	 * @param Document $couchDbDocument
	 * @param array $options
	 * @return array
	 */
	protected function buildRequestOptions($options, Document $couchDbDocument = null)
	{
        if (isset($couchDbDocument)) {
            $requestOptions = [
                'json' => $couchDbDocument->getColumnsAndData()
            ];
        }

		if (isset($options['authToken'])) {
			$requestOptions['headers'] = ['X-CouchDB-WWW-Authenticate' => 'Cookie'];
			$requestOptions['cookies'] = $this->buildAuthCookie($requestOptions['authToken']);
		}

		if (isset($options['user'])) {
			$requestOptions['auth'] = [
				$options['user']['username'],
                $options['user']['password']
			];
		}

		if ($this->debug) {
			$requestOptions['debug'] = true;
		}

		return $requestOptions;
	}
}