<?PHP

namespace CouchDbAdapter\CouchDb;
use CouchDbAdapter\Client\Client;
use InvalidArgumentException;

/**
 * Special class to handle administration tasks
 * - Create administrators
 * - Create users
 * - Create roles
 * - Assign roles to users
 */
class Admin
{
	/** @var Server */
	private $server;

	/** @var Client */
	private $client;

	/** @var string */
	private $usersDb;

	/** @var array */
	private $options = [];

	/**
	 * @param Server $server
	 * @param string $user
	 * @param string $password
	 */
	public function __construct(Server $server, $user, $password)
	{
		$this->server = $server;
		$this->client = $server->getClient();
		$this->setAdminUser($user, $password);
	}


	/**
	 * Returns the security object of a database
	 *
	 * @link http://wiki.apache.org/couchdb/Security_Features_Overview
	 * @return stdClass security object of the database
	 * @throws couchException
	 */
	public function getSecurity()
	{
		$dbname = $this->client->getDatabaseName();
		$raw = $this->client->query(
			"GET",
			"/" . $dbname . "/_security"
		);

		$resp = couch::parseRawResponse($raw);

		if ($resp['status_code'] != 200) {
			throw new couchException($raw);
		}

		if (!property_exists($resp['body'], "admins")) {
			$resp["body"]->admins = new stdClass();
			$resp["body"]->admins->names = array();
			$resp["body"]->admins->roles = array();
			$resp["body"]->readers = new stdClass();
			$resp["body"]->readers->names = array();
			$resp["body"]->readers->roles = array();
		}

		return $resp['body'];
	}

	/**
	 * set the security object of a database
	 *
	 * @link http://wiki.apache.org/couchdb/Security_Features_Overview
	 * @param stdClass $security the security object to apply to the database
	 * @return stdClass CouchDB server response ( { "ok": true } )
	 * @throws InvalidArgumentException|couchException
	 */
	public function setSecurity($security)
	{
		if (!is_object($security)) {
			throw new InvalidArgumentException("Security should be an object");
		}
		$dbname = $this->client->getDatabaseName();
		$raw = $this->client->query(
			"PUT",
			"/" . $dbname . "/_security",
			array(),
			json_encode($security)
		);
		$resp = couch::parseRawResponse($raw);
		if ($resp['status_code'] == 200) {
			return $resp['body'];
		}
		throw new couchException($raw);
	}

	/**
	 * add a user to the list of readers for the current database
	 *
	 * @param string $login user login
	 * @return boolean true if the user has successfuly been added
	 * @throws InvalidArgumentException
	 */
	public function addDatabaseReaderUser($login)
	{
		if (strlen($login) < 1) {
			throw new InvalidArgumentException("Login can't be empty");
		}
		$sec = $this->getSecurity();
		if (in_array($login, $sec->readers->names)) {
			return true;
		}
		array_push($sec->readers->names, $login);
		$back = $this->setSecurity($sec);
		if (is_object($back) && property_exists($back, "ok") && $back->ok == true) {
			return true;
		}

		return false;
	}

	/**
	 * add a user to the list of admins for the current database
	 *
	 * @param string $login user login
	 * @return boolean true if the user has successfuly been added
	 * @throws InvalidArgumentException
	 */
	public function addDatabaseAdminUser($login)
	{
		if (strlen($login) < 1) {
			throw new InvalidArgumentException("Login can't be empty");
		}
		$sec = $this->getSecurity();
		if (in_array($login, $sec->admins->names)) {
			return true;
		}
		array_push($sec->admins->names, $login);
		$back = $this->setSecurity($sec);
		if (is_object($back) && property_exists($back, "ok") && $back->ok == true) {
			return true;
		}

		return false;
	}

	/**
	 * get the list of admins for the current database
	 *
	 * @return array database admins logins
	 */
	public function getDatabaseAdminUsers()
	{
		$sec = $this->getSecurity();

		return $sec->admins->names;
	}

	/**
	 * get the list of readers for the current database
	 *
	 * @return array database readers logins
	 */
	public function getDatabaseReaderUsers()
	{
		$sec = $this->getSecurity();

		return $sec->readers->names;
	}

	/**
	 * remove a user from the list of readers for the current database
	 *
	 * @param string $login user login
	 * @return boolean true if the user has successfuly been removed
	 * @throws InvalidArgumentException
	 */
	public function removeDatabaseReaderUser($login)
	{
		if (strlen($login) < 1) {
			throw new InvalidArgumentException("Login can't be empty");
		}
		$sec = $this->getSecurity();
		if (!in_array($login, $sec->readers->names)) {
			return true;
		}
		$sec->readers->names = $this->rmFromArray($login, $sec->readers->names);
		$back = $this->setSecurity($sec);
		if (is_object($back) && property_exists($back, "ok") && $back->ok == true) {
			return true;
		}

		return false;
	}

	/**
	 * remove a user from the list of admins for the current database
	 *
	 * @param string $login user login
	 * @return boolean true if the user has successfuly been removed
	 * @throws InvalidArgumentException
	 */
	public function removeDatabaseAdminUser($login)
	{
		if (strlen($login) < 1) {
			throw new InvalidArgumentException("Login can't be empty");
		}
		$sec = $this->getSecurity();
		if (!in_array($login, $sec->admins->names)) {
			return true;
		}
		$sec->admins->names = $this->rmFromArray($login, $sec->admins->names);
		$back = $this->setSecurity($sec);
		if (is_object($back) && property_exists($back, "ok") && $back->ok == true) {
			return true;
		}

		return false;
	}

/// roles

	/**
	 * add a role to the list of readers for the current database
	 *
	 * @param string $role role name
	 * @return boolean true if the role has successfuly been added
	 * @throws InvalidArgumentException
	 */
	public function addDatabaseReaderRole($role)
	{
		if (strlen($role) < 1) {
			throw new InvalidArgumentException("Role can't be empty");
		}
		$sec = $this->getSecurity();
		if (in_array($role, $sec->readers->roles)) {
			return true;
		}
		array_push($sec->readers->roles, $role);
		$back = $this->setSecurity($sec);
		if (is_object($back) && property_exists($back, "ok") && $back->ok == true) {
			return true;
		}

		return false;
	}

	/**
	 * add a role to the list of admins for the current database
	 *
	 * @param string $role role name
	 * @return boolean true if the role has successfuly been added
	 * @throws InvalidArgumentException
	 */
	public function addDatabaseAdminRole($role)
	{
		if (strlen($role) < 1) {
			throw new InvalidArgumentException("Role can't be empty");
		}
		$sec = $this->getSecurity();
		if (in_array($role, $sec->admins->roles)) {
			return true;
		}
		array_push($sec->admins->roles, $role);
		$back = $this->setSecurity($sec);
		if (is_object($back) && property_exists($back, "ok") && $back->ok == true) {
			return true;
		}

		return false;
	}

	/**
	 * get the list of admin roles for the current database
	 *
	 * @return array database admins roles
	 */
	public function getDatabaseAdminRoles()
	{
		$sec = $this->getSecurity();

		return $sec->admins->roles;
	}

	/**
	 * get the list of reader roles for the current database
	 *
	 * @return array database readers roles
	 */
	public function getDatabaseReaderRoles()
	{
		$sec = $this->getSecurity();

		return $sec->readers->roles;
	}

	/**
	 * remove a role from the list of readers for the current database
	 *
	 * @param string $role role name
	 * @return boolean true if the role has successfuly been removed
	 * @throws InvalidArgumentException
	 */
	public function removeDatabaseReaderRole($role)
	{
		if (strlen($role) < 1) {
			throw new InvalidArgumentException("Role can't be empty");
		}
		$sec = $this->getSecurity();
		if (!in_array($role, $sec->readers->roles)) {
			return true;
		}
		$sec->readers->roles = $this->rmFromArray($role, $sec->readers->roles);
		$back = $this->setSecurity($sec);
		if (is_object($back) && property_exists($back, "ok") && $back->ok == true) {
			return true;
		}

		return false;
	}

	/**
	 * remove a role from the list of admins for the current database
	 *
	 * @param string $role role name
	 * @return boolean true if the role has successfuly been removed
	 * @throws InvalidArgumentException|couchException
	 */
	public function removeDatabaseAdminRole($role)
	{
		if (strlen($role) < 1) {
			throw new InvalidArgumentException("Role can't be empty");
		}
		$sec = $this->getSecurity();
		if (!in_array($role, $sec->admins->roles)) {
			return true;
		}
		$sec->admins->roles = $this->rmFromArray($role, $sec->admins->roles);
		$back = $this->setSecurity($sec);
		if (is_object($back) && property_exists($back, "ok") && $back->ok == true) {
			return true;
		}

		return false;
	}

/// /roles

	private function rmFromArray($needle, $haystack)
	{
		$back = array();
		foreach ($haystack as $one) {
			if ($one != $needle) {
				$back[] = $one;
			}
		}

		return $back;
	}

}
