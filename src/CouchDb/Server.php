<?php

namespace CouchDbAdapter\CouchDb;

use CouchDbAdapter\Client\Client;

class Server
{
	/** @var Client */
	private $client;

	/** @var string */
	private $host;

	/** @var integer */
	private $port;

	/** @var boolean */
	private $https;

	/** @var array */
	private $options = [];

	/**
	 * @param Client $client
	 * @param string $host
	 * @param integer $port
	 * @param bool $https
	 */
	public function __construct(Client $client, $host, $port = 5984, $https = false)
	{
		$this->client = $client;
		$this->host = $host;
		$this->port = $port;
		$this->https = $https;
	}

	/**
	 * @return Client
	 */
	public function getClient()
	{
		return $this->client;
	}

	/**
	 * @param string $token
	 */
	public function setAuthCookieToken($token)
	{
		$this->options['authToken'] = [
			'cookieName' => 'AuthSession',
			'cookieValue' => $token,
			'cookieDomain' => $this->host
		];
	}

	/**
	 * @return array
	 */
	public function getOptions()
	{
		return $this->options;
	}

	/**
	 * @param string $name
	 * @return Database
	 */
	public function __get($name)
	{
		return new Database($this->client, $this, $name);
	}

	/**
	 * Check that the server responds
	 */
	public function ping()
	{
		return $this->client->get($this->getUrl(), [200], $this->options);
	}

	/**
	 * @return array
	 */
	public function listDbs()
	{
		return $this->client->get($this->getUrl() . '/_all_dbs', [200], $this->options);
	}

	/**
	 * @param string $name
	 * @return Database
	 */
	public function createDb($name)
	{
		$this->client->put($this->getUrl() . '/' . urlencode($name) . '/', [200], $this->options);
		return $this->$name;
	}

	/**
	 * @return string
	 */
	public function getUrl()
	{
		return 'http' . ($this->https ? 's' : '') . "://{$this->host}:{$this->port}";
	}
}