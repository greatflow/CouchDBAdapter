<?php

namespace CouchDbAdapter\CouchDb;


class Document
{
	/** @var Client */
	private $client;

	/** @var Database */
	private $database;

	/** @var array */
	private $columns = [];

	/**
	 * @param Client $client
	 * @param Database $database
	 */
	public function __construct(Client $client, Database $database)
	{
		$this->client = $client;
		$this->database = $database;
	}

	/**
	 * @param string $name
	 * @param string $value
	 */
	public function __set($name, $value)
	{
		if ($name = '_id' && isset($this->columns['_id'])) {
			throw new \InvalidArgumentException('Document id is immutable');
		}

		$this->columns[$name] = (string) $value;
	}

	/**
	 * @param string $name
	 * @return mixed
	 */
	public function _get($name)
	{
		if (isset($this->columns[$name])) {
			return $this->columns[$name];
		}

		return false;
	}

	/**
	 * @param array $data [ColumnName => Value]
	 */
	public function populateFromArray(array $data)
	{
		foreach ($data as $column => $value) {
			$this->columns[$column] = $value;
		}
	}

	/**
	 * @return array
	 */
	public function getColumnsAndData()
	{
		return $this->columns;
	}

	/**
	 * Create a new document with the same properties as this one
	 *
	 * @param string [$id]
	 * @return Document
	 */
	public function dupe($id = null)
	{
		$columnsCopy = $this->columns;
		foreach ($columnsCopy as $column => $value) {
			if ($column[0] == '_') {
				unset($columnsCopy[$column]);
			}
		}

		return $this->database->createDoc($id)->populateFromArray($columnsCopy);
	}

	/**
	 * Save the document
	 */
	public function save()
	{
		if (isset($this->columns['_id'])) {
			$response = $this->client->put($this->database->getDocUrl($this->_id), [201]);
		} else {
			$response = $this->client->post($this->database->getUrl(), $this);
			$this->_id = $response->id;
		}

		$this->_rev = $response->rev;
	}

	/**
	 * Delete the document from the database
	 */
	public function delete()
	{
		if (!isset($this->columns['_id'])) {
			throw new BadMethodCallException("Cannot delete document without an ID");
		}

		if (!isset($this->columns['_rev'])) {
			throw new BadMethodCallException("Cannot delete document without a revision number");
		}

		$this->client->delete($this->database->getDocUrl($this->_id, array('rev' => $this->_rev)));

		unset($this->columns['_rev']);
	}

	/**
	 * Fetch the data for an attachment
	 *
	 * @param string|array $name
	 * @return string
	 */
	public function getAttachment($name)
	{
		$name = (array)$name;

		$attachment = $this->_attachments[implode('/', $name)];
		if (!$attachment) return null;

		if ($attachment->stub) {
			return $this->client->rawRequest('GET', $this->database->getAttachmentUrl($this->_id, $name));
		} else {
			return base64_decode($attachment->data);
		}
	}

	/**
	 * Create an inline attachment, to be saved with the doc
	 *
	 * @param string|array $name
	 * @param string $contentType
	 * @param string $data
	 */
	public function setAttachment($name, $contentType, $data)
	{
		if (!isset($this->_data['_attachments'])) {
			$this->_data['_attachments'] = new Dwin_Document_Node;
		}

		if (is_array($name)) $name = implode('/', $name);

		$this->_attachments[$name] = array(
			"content_type" => $contentType,
			"data" => base64_encode($data)
		);
	}

	/**
	 * Remove attachment from the in-memory copy of the doc
	 *
	 * @param string|array $name
	 */
	public function unsetAttachment($name)
	{
		if (is_array($name)) $name = implode('/', $name);
		unset($this->_attachments[$name]);

		if (count($this->_attachments) == 0) {
			unset($this->_data['_attachments']);
		}
	}
}