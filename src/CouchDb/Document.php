<?php

namespace CouchDbAdapter\CouchDb;


class Document
{

    const RESERVED_COLUMNS = ['_id', '_rev', '_attachments', '_deleted'];

	/** @var array */
	private $columns = [];

    public function __set($name, $value)
    {
        preg_match("/^set(.*)/", $name, $matches);

        if (isset($matches[1]) && ! in_array($matches[1], self::RESERVED_COLUMNS)) {
            $this->columns[lcfirst($matches[1])] = $value;
            return $this;
        }
    }

    public function __get($name)
    {
        preg_match("/^get(.*)/", $name, $matches);

        if (isset($matches[1]) && ! in_array($matches[1], self::RESERVED_COLUMNS)) {
            if (isset($this->columns[$name])) {
                return $this->columns[$name];
            }
        }
    }

    public function setId($id)
    {
        if (isset($this->columns['_id'])) {
            throw new \InvalidArgumentException('Document id is immutable');
        }

        $this->columns['_id'] = $id;

        return $this;
    }

    public function getId()
    {
        return $this->columns['_id'];
    }

    public function setSoftDelete()
    {
        $this->columns['_deleted'] = true;

        return $this;
    }

    public function setRev($rev)
    {
        $this->columns['_rev'] = $rev;

        return $this;
    }

    public function getRev()
    {
        return $this->columns['_rev'];
    }

    public function setAttachment($attachment)
    {
        $this->columns['_attachments'] = $attachment;

        return $this;
    }

    public function getAttachment()
    {
        return $this->columns['_attachments'];
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

//	/**
//	 * Fetch the data for an attachment
//	 *
//	 * @param string|array $name
//	 * @return string
//	 */
//	public function getAttachment($name)
//	{
//		$name = (array)$name;
//
//		$attachment = $this->_attachments[implode('/', $name)];
//		if (!$attachment) return null;
//
//		if ($attachment->stub) {
//			return $this->client->rawRequest('GET', $this->database->getAttachmentUrl($this->_id, $name));
//		} else {
//			return base64_decode($attachment->data);
//		}
//	}
//
//	/**
//	 * Create an inline attachment, to be saved with the doc
//	 *
//	 * @param string|array $name
//	 * @param string $contentType
//	 * @param string $data
//	 */
//	public function setAttachment($name, $contentType, $data)
//	{
//		if (!isset($this->_data['_attachments'])) {
//			$this->_data['_attachments'] = new Dwin_Document_Node;
//		}
//
//		if (is_array($name)) $name = implode('/', $name);
//
//		$this->_attachments[$name] = array(
//			"content_type" => $contentType,
//			"data" => base64_encode($data)
//		);
//	}
//
//	/**
//	 * Remove attachment from the in-memory copy of the doc
//	 *
//	 * @param string|array $name
//	 */
//	public function unsetAttachment($name)
//	{
//		if (is_array($name)) $name = implode('/', $name);
//		unset($this->_attachments[$name]);
//
//		if (count($this->_attachments) == 0) {
//			unset($this->_data['_attachments']);
//		}
//	}
}