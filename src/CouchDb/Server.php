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

    /** @var string */
    private $usersDb;

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
     * @param string $username
     * @param string $password
     */
    public function setAdminUser($username, $password)
    {
        $this->options['user'] = [
            'username' => $username,
            'password' => $password
        ];
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
	 * Check that the server responds
	 */
	public function ping()
	{
		return $this->client->get($this->getServerUrl(), 200, $this->getOptions());
	}

	/**
	 * @return array
	 */
	public function listDbs()
	{
		return $this->client->get($this->getServerUrl() . '/_all_dbs', 200, $this->getOptions());
	}

	/**
	 * @param string $name
	 * @return Database
	 */
	public function createDatabase($name)
	{
		$this->client->put($this->getServerUrl() . '/' . urlencode($name) . '/', 201, $this->getOptions());
		return $this->getDatabase($name);
	}

    /**
     * Creates a new CouchDB server administrator
     *
     * @param string $username administrator username
     * @param string $password administrator password
     * @throws InvalidArgumentException|Exception|CouchDbException
     */
    public function createAdmin($username, $password)
    {
        $username = urlencode($username);
        $this->options['json'] = (string) $password;

        if (strlen($username) < 1) {
            throw new InvalidArgumentException("Username can't be empty");
        }

        if (strlen($password) < 1) {
            throw new InvalidArgumentException("Password can't be empty");
        }

        $url = $this->getServerUrl() . '/_config/admins/' . urlencode($username);

        $this->client->put($url, 200, $this->getOptions());
    }

    /**
     * Permanently removes a CouchDB Server administrator
     *
     * @param string $username administrator username
     * @throws InvalidArgumentException
     */
    public function deleteAdmin($username)
    {
        $username = urlencode($username);

        if (strlen($username) < 1) {
            throw new InvalidArgumentException("Username can't be empty");
        }

        $url = $this->getServerUrl() . '/_config/admins/' . urlencode($username);
        $this->client->delete($url, 200, $this->getOptions());
    }

    /**
     * @param string $username
     * @param string $password
     * @param array $roles
     * @throws InvalidArgumentException
     */
    public function createUser($username, $password, $roles = array())
    {
        if (strlen($username) < 1) {
            throw new InvalidArgumentException("Username can't be empty");
        }

        if (strlen($password) < 1) {
            throw new InvalidArgumentException("Password can't be empty");
        }

        $document = new Document();
        $document->setName($username)
            ->setType('user')
            ->setRoles($roles)
            ->setPassword((string) $password);

        $url = $this->getServerUrl() . "/_users/org.couchdb.user:{$username}";
        $this->client->put($url, 201, $this->getOptions(), $document);
    }

    /**
     * @param string $username
     * @throws InvalidArgumentException
     */
    public function deleteUser($username)
    {
        if (strlen($username) < 1) {
            throw new InvalidArgumentException("Username can't be empty");
        }

        $database = $this->getDatabase('_users');
        $userDocument = $database->getDocumentById("org.couchdb.user:{$username}");

        $database->deleteDocument($userDocument);
    }

	/**
	 * @return string
	 */
	public function getServerUrl()
	{
		return 'http' . ($this->https ? 's' : '') . "://{$this->host}:{$this->port}";
	}
}