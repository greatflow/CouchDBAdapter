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
	 * @param string $newDocumentId
	 * @param string $rev
     * @return Document
	 */
	public function copyDocument($id, $newDocumentId, $rev = null)
	{
        $options = $this->server->getOptions();
        $options['headers'] = ['Destination' => $newDocumentId . ($rev ? "?rev={$rev}" : '')];

		$this->client->copy($this->getDocUrl($id), 201, $options);

		return $this->getDocument($newDocumentId);
	}

	public function getAllDocuments()
	{
		return $this->client->get($this->getUrl() . '/_all_docs', 200, $this->server->getOptions());
	}

    public function saveDocument(Document $document)
    {
        $data = $document->getColumnsAndData();
        if (isset($data['_id'])) {
            $response = $this->client->put($this->getDocUrl($data['_id']), 201, $this->server->getOptions(), $document);
        } else {
            $response = $this->client->post($this->getUrl(), 201, $this->server->getOptions(), $document);
            $document->setId($response['id']);
        }

        $document->setRev($response['rev']);
    }

    public function deleteDocument(Document $document)
    {
        $data = $document->getColumnsAndData();
        if (! isset($data['_id'])) {
            throw new BadMethodCallException("Cannot delete document without an ID");
        }

        if (! isset($data['_rev'])) {
            throw new BadMethodCallException("Cannot delete document without a revision number");
        }

        $this->client->delete($this->getDocUrl($data['_id'], array('rev' => $document->getRev())), 200, $this->server->getOptions());

        $document->unsetRev();
    }

	/**
	 * Delete this database
	 */
	public function deleteDatabase()
	{
		$this->client->delete($this->getUrl() . '/', 200, $this->server->getOptions());
	}

    public function getDocument($id)
    {
        try {
            $response = $this->client->get($this->getDocUrl($id), 200, $this->server->getOptions());

            $document = new Document();
            $document->populateFromArray($response);
            return $document;
        } catch (Exception $e) {
            return false;
        }
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