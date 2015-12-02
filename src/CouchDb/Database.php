<?php

namespace CouchDbAdapter\CouchDb;

use CouchDbAdapter\Client\Client;
use CouchDbAdapter\Exceptions\CouchDbException;

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

	public function getAllDocuments($includeDocs = false)
	{
        $url = $this->getDatabaseUrl() . '/_all_docs';
        if ($includeDocs) {
            $url .= '?include_docs=true';
        }
		$response =  $this->client->get($url, 200, $this->server->getOptions());

        if ($includeDocs) {
            $documentCollection = new DocumentCollection();
            foreach ($response['rows'] as $documentArray) {
                $document = new Document();
                $document->populateFromArray($documentArray['doc']);
                $documentCollection->addDocument($document);
            }
            $response = $documentCollection;
        }

        return $response;
	}

    /**
     * Saves or updates the document to the database
     * @param Document $document
     */
    public function saveDocument(Document $document)
    {
        $data = $document->getData();
        if (isset($data['_id'])) {
            $response = $this->client->put($this->getDocumentUrl($data['_id']), 201, $this->server->getOptions(), $document);
        } else {
            $response = $this->client->post($this->getDatabaseUrl(), 201, $this->server->getOptions(), $document);
            $document->setId($response['id']);
        }

        $document->setRev($response['rev']);
    }

    /**
     * Deletes the document. CouchDB will still retain a copy though
     * @param Document $document
     * @throws BadMethodCallException
     */
    public function deleteDocument(Document $document)
    {
        $data = $document->getData();
        if (! isset($data['_id'])) {
            throw new BadMethodCallException("Cannot delete document without an ID");
        }

        if (! isset($data['_rev'])) {
            throw new BadMethodCallException("Cannot delete document without a revision number");
        }

        $this->client->delete($this->getDocumentUrl($data['_id'], array('rev' => $document->getRev())), 200, $this->server->getOptions());
        $document->unsetRev();
    }

	/**
	 * Delete this database
	 */
	public function deleteDatabase()
	{
		$this->client->delete($this->getDatabaseUrl() . '/', 200, $this->server->getOptions());
	}

    /**
     * @param $id
     * @return Document
     *
     * @throws CouchDbException
     */
    public function getDocumentById($id)
    {
        try {
            $response = $this->client->get($this->getDocumentUrl($id), 200, $this->server->getOptions());
            $document = new Document();
            $document->populateFromArray($response);
            return $document;
        } catch (CouchDbException $exception) {
            // If exception was a 404 the document was not found. So we can create one using $id
            if ($exception->getCode() == 404) {
                $document = new Document($id);
                return $document;
            } else {
                throw $exception;
            }
        }
    }

	/**
	 * @return string
	 */
	public function getDatabaseUrl()
	{
		return $this->server->getUrl() . '/' . urlencode($this->dbName);
	}

	/**
	 * @param string $id
	 * @param array $args
	 * @return string
	 */
	public function getDocumentUrl($id, $args = array())
	{
		return $this->getDatabaseUrl() . '/' . urlencode($id)  . ($args ? '?' . http_build_query($args) : '');
	}
}