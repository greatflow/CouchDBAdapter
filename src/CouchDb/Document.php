<?php

namespace CouchDbAdapter\CouchDb;


class Document
{
	/**
	 * Create a new document with the same properties as this one
	 *
	 * @param string [$id]
	 * @return Dwin_Couch_Document
	 */
	public function dupe($id = null)
	{
		$data = $this->_data;
		foreach ($data as $key => $value) if ($key[0] == '_') unset($data[$key]);
		return $this->_db->createDoc($id)->populate($data);
	}

	/**
	 * Save the document (back) to the database
	 */
	public function save()
	{
		if (isset($this->_id)) {
			$response = $this->_client->put($this->_db->getDocUrl($this->_id), $this);
		} else {
			$response = $this->_client->post($this->_db->getUrl(), $this);
			$this->_data['_id'] = $response->id;
		}

		$this->_data['_rev'] = $response->rev;
	}

	/**
	 * Delete the document from the database
	 */
	public function delete()
	{
		if (!isset($this->_id)) {
			throw new BadMethodCallException("Cannot delete document without an ID");
		}

		if (!isset($this->_rev)) {
			throw new BadMethodCallException("Cannot delete document without a revision number");
		}

		$this->_client->delete($this->_db->getDocUrl($this->_id, array('rev' => $this->_rev)));

		unset($this->_data['_rev']);
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
			return $this->_client->rawRequest('GET', $this->_db->getAttachmentUrl($this->_id, $name));
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