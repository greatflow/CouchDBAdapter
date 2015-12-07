<?php

namespace CouchDbAdapter\CouchDb;

use CouchDbAdapter\Client\Client;
use InvalidArgumentException;

class Server
{
	/** @var Client */
	protected $client;

	/** @var string */
	private $host;

	/** @var integer */
	private $port;

	/** @var boolean */
	private $https;

	/** @var array */
    protected $options = [];

    /** @var string */
    protected $usersDb;

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
     * Set the name of the users database
     *
     * @param string $name
     */
    public function setUsersDatabase($name)
    {
        $this->usersDb = $name;
    }

    /**
     * Get the name of the users database this class will use
     * If nothing is set, use the couchDB default `_users`
     *
     * @return string
     */
    public function getUsersDatabase()
    {
        if (empty($this->usersDb)) {
            $this->setUsersDatabase('_users');
        }

        return $this->usersDb;
    }

	/**
	 * @return Client
	 */
	public function getClient()
	{
		return $this->client;
	}

    /**
     * @param $username
     * @param $password
     * @return string
     */
    public function getAuthToken($username, $password)
    {
        $this->options['getAuthToken'] = [
            'name' => $username,
            'password' => $password,
        ];

        $this->client->post($this->getServerUrl() . '/_session', 200, $this->getOptions());

        $headers = $this->client->getLastHeaders();
        $cookie = $headers['Set-Cookie'][0];
        list($tokenPart) = explode(';', $cookie);
        $tokenPart = explode('=', $tokenPart);

        return $tokenPart[1];
    }

	/**
	 * @param string $token
	 */
	public function setAuthCookieToken($token)
	{
		$this->options['setAuthToken'] = [
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
	public function getDatabase($name)
	{
		return new Database($this->client, $this, $name);
	}

	/**
	 * @return array
	 */
	public function listDbs()
	{
		return $this->client->get($this->getServerUrl() . '/_all_dbs', 200, $this->getOptions());
	}

	/**
	 * @return string
	 */
	public function getServerUrl()
	{
		return 'http' . ($this->https ? 's' : '') . "://{$this->host}:{$this->port}";
	}
}