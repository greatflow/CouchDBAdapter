<?php

namespace CouchDbAdapter\CouchDb;

use InvalidArgumentException;

class Document
{

    const RESERVED_COLUMNS = ['_id', '_rev', '_attachments', '_deleted'];

	/** @var array */
	private $columns = [];

    public function __construct($id = null)
    {
        if (isset($id)) {
            $this->setId($id);
        }
    }

    /**
     *
     * @param $name
     * @param $value
     * @return mixed
     */
    public function __call($name, $value)
    {
        if (strpos($name, 'set') !== false) {
            return $this->setColumn($name, $value[0]);
        } elseif (strpos($name, 'get') !== false) {
            return $this->getColumn($name);
        }

    }

    /**
     * @param $name
     * @param $value
     * @return $this
     */
    private function setColumn($name, $value)
    {
        $columnName = lcfirst(substr($name, 3));

        if (in_array($columnName, self::RESERVED_COLUMNS)) {
            $method = 'set' . ucfirst(ltrim($columnName, '_'));
            throw new InvalidArgumentException("$columnName is a reserved key. Please use the $method method");
        }

        $this->columns[$columnName] = $value;
        return $this;
    }

    /**
     * @param $name
     * @return mixed
     */
    private function getColumn($name)
    {
        $columnName = lcfirst(substr($name, 3));

        if (in_array($columnName, self::RESERVED_COLUMNS)) {
            $method = 'get' . ucfirst(ltrim($columnName, '_'));
            throw new InvalidArgumentException("$columnName is a reserved key. Please use the $method method");
        }

        if (isset($this->columns[$columnName])) {
            return $this->columns[$columnName];
        }
    }

    public function setId($id)
    {
        if (isset($this->columns['_id'])) {
            throw new InvalidArgumentException('Document id is immutable');
        }

        $this->columns['_id'] = (string) $id;

        return $this;
    }

    public function getId()
    {
        if (isset($this->columns['_id'])) {
            return $this->columns['_id'];
        }
    }

    public function setRev($rev)
    {
        $this->columns['_rev'] = $rev;

        return $this;
    }

    public function getRev()
    {
        if (isset($this->columns['_rev'])) {
            return $this->columns['_rev'];
        }
    }

    public function unsetRev()
    {
        unset($this->columns['_rev']);
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