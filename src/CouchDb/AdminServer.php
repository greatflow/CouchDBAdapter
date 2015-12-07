<?php

namespace CouchDbAdapter\CouchDb;

class AdminServer extends Server
{
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

        if ((strlen($username) < 1) || (strlen($password) < 1)) {
            throw new InvalidArgumentException("Details can't be empty");
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
        if ((strlen($username) < 1) || (strlen($password) < 1)) {
            throw new InvalidArgumentException("Details can't be empty");
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
     * @param $dbName
     * @return Document
     */
    private function getSecurityDocument($dbName)
    {
        $url = $this->getServerUrl() . "/{$dbName}/_security";
        $response = $this->client->get($url, 200, $this->getOptions());

        $securityDocument = new Document();
        $securityDocument->populateFromArray($response);

        $emptyGroup = ['names' => [], 'roles' => []];

        if (is_null($securityDocument->getMembers())) {
            $securityDocument->setMembers($emptyGroup);
        }

        if (is_null($securityDocument->getAdmins())) {
            $securityDocument->setAdmins($emptyGroup);
        }

        return $securityDocument;
    }

    /**
     * @param Document $securityDocument
     * @param string $dbName
     * @return ResponseInterface
     */
    private function setSecurityDocument(Document $securityDocument, $dbName)
    {
        $url = $this->getServerUrl() . "/{$dbName}/_security";
        return $this->client->put($url, 200, $this->getOptions(), $securityDocument);
    }

    /**
     * @param $dbName
     * @param $username
     * @param array $roles
     * @throws InvalidArgumentException
     */
    public function addDatabaseAdmin($dbName, $username, $roles = [])
    {
        if (strlen($username) < 1) {
            throw new InvalidArgumentException("Username can't be empty");
        }

        $securityDocument = $this->getSecurityDocument($dbName);

        $admins = $securityDocument->getAdmins();
        $admins = $this->appendToUserGroup($admins, $username, $roles);
        $securityDocument->setAdmins($admins);

        $this->setSecurityDocument($securityDocument, $dbName);
    }

    /**
     * @param $dbName
     * @param $username
     * @param array $roles
     * @throws InvalidArgumentException
     */
    public function removeDatabaseAdmin($dbName, $username, $roles = [])
    {
        if (strlen($username) < 1) {
            throw new InvalidArgumentException("Username can't be empty");
        }

        $securityDocument = $this->getSecurityDocument($dbName);

        $admins = $securityDocument->getAdmins();
        $admins = $this->removeFromUserGroup($admins, $username, $roles);
        $securityDocument->setAdmins($admins);

        $this->setSecurityDocument($securityDocument, $dbName);
    }

    /**
     * @param $dbName
     * @param $username
     * @param array $roles
     * @throws InvalidArgumentException
     */
    public function addDatabaseMember($dbName, $username, $roles = [])
    {
        if (strlen($username) < 1) {
            throw new InvalidArgumentException("Username can't be empty");
        }

        $securityDocument = $this->getSecurityDocument($dbName);

        $members = $securityDocument->getMembers();
        $members = $this->appendToUserGroup($members, $username, $roles);
        $securityDocument->setMembers($members);

        $this->setSecurityDocument($securityDocument, $dbName);
    }

    /**
     * @param $dbName
     * @param $username
     * @param array $roles
     * @throws InvalidArgumentException
     */
    public function removeDatabaseMember($dbName, $username, $roles = [])
    {
        if (strlen($username) < 1) {
            throw new InvalidArgumentException("Username can't be empty");
        }

        $securityDocument = $this->getSecurityDocument($dbName);

        $members = $securityDocument->getMembers();
        $members = $this->removeFromUserGroup($members, $username, $roles);

        $securityDocument->setMembers($members);
        $this->setSecurityDocument($securityDocument, $dbName);
    }

    /**
     * @param array $userGroup
     * @param string $username
     * @param array $roles
     * @return array
     */
    private function appendToUserGroup(array $userGroup, $username, array $roles)
    {
        if (! in_array($username, $userGroup['names'])) {
            array_push($userGroup['names'], $username);
        }

        $userGroup['roles'] = array_unique(array_merge($userGroup['roles'], $roles));

        return $userGroup;
    }

    /**
     * @param array $userGroup
     * @param $username
     * @param array $roles
     * @return array|bool
     */
    private function removeFromUserGroup(array $userGroup, $username, array $roles)
    {
        if (! in_array($username, $userGroup['names'])) {
            return true;
        }

        $names = array_filter($userGroup['names'], function($userNames) use ($username) {
            if ($userNames != $username) {
                return true;
            }
        });


        $roles = array_filter($userGroup['roles'], function($userRoles) use ($roles) {
            if (! in_array($userRoles, $roles)) {
                return true;
            }
        });

        // remove keys
        sort($names);
        sort($roles);
        $userGroup['names'] = $names;
        $userGroup['roles'] = $roles;

        return $userGroup;
    }
}