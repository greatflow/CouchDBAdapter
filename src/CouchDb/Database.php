<?php

namespace CouchDbAdapter\CouchDb;

use CouchDbAdapter\Client\Client;

class Database
{
	/** @var Client */
	private $client;

	/** @var Server */
	private $server;

	/** @var string */
	private $dbName;

	/**
	 * @param Client $client
	 * @param Server $server
	 * @param string $dbName
	 */
	public function __construct(Client $client, Server $server, $dbName)
	{
		$this->client = $client;
		$this->server = $server;
		$this->dbName = $dbName;
	}

	/**
	 * @param string $id
	 * @return Document
	 */
	public function createDoc($id = null)
	{
		$document = new Document($this->client, $this);

		if (! is_null($id)) {
			$document->_id = (string) $id;
		}

		return $document;
	}

	/**
	 * @param string $id
	 * @param string $newId
	 * @param string $rev
	 */
	public function copyDoc($id, $newId, $rev = null)
	{
		$result = $this->client->copy(
			$this->getDocUrl($id), null,
			array('Destination' => $newId . ($rev ? "?rev={$rev}" : ''))
		);
		return $result->rev;
	}

	public function getAllDocs()
	{
		return $this->client->get($this->getUrl() . '/_all_docs', [200], $this->server->getOptions());
	}

	/**
	 * @param string $id
	 * @param string $rev
	 */
	public function deleteDoc($id, $rev = null)
	{
		if (! $rev) {
			$rev = $this->getCurrentRevision($id);
		}

		$this->client->delete($this->getDocUrl($id, array('rev' => $rev)), [200], $this->server->getOptions());
	}


	/**
	 * Delete this database
	 */
	public function deleteDatabase()
	{
		$this->client->delete($this->getUrl() . '/', [200], $this->server->getOptions());
	}

	/**
	 * Gets the current revision of a document without fetching it
	 *
	 * @param string $id
	 * @return string
	 */
	public function getCurrentRevision($id)
	{
		return json_decode($this->client->head($this->getDocUrl($id), [200]), $this->server->getOptions());
	}

	/**
	 * @return string
	 */
	public function getUrl()
	{
		return $this->server->getUrl() . '/' . urlencode($this->dbName);
	}

	/**
	 * @param string $id
	 * @param array $args
	 * @return string
	 */
	public function getDocUrl($id, $args = array())
	{
		return $this->getUrl() . '/' . urlencode($id)  . ($args ? '?' . http_build_query($args) : '');
	}
}