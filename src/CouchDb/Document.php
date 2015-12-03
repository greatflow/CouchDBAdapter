<?php

namespace CouchDbAdapter\CouchDb;

use InvalidArgumentException;

class Document
{

    const RESERVED_COLUMNS = ['_id', '_rev', '_attachments', '_deleted'];

	/** @var array */
	private $data = [];

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

        $this->data[$columnName] = $value;
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

        if (isset($this->data[$columnName])) {
            return $this->data[$columnName];
        }
    }

    public function setId($id)
    {
        if (isset($this->data['_id'])) {
            throw new InvalidArgumentException('Document id is immutable');
        }

        $this->data['_id'] = (string) $id;

        return $this;
    }

    public function getId()
    {
        if (isset($this->data['_id'])) {
            return $this->data['_id'];
        }
    }

    public function setRev($rev)
    {
        $this->data['_rev'] = (string) $rev;

        return $this;
    }

    public function getRev()
    {
        if (isset($this->data['_rev'])) {
            return $this->data['_rev'];
        }
    }

    public function unsetRev()
    {
        unset($this->data['_rev']);
    }

    public function setAttachment($attachment)
    {
        $this->data['_attachments'] = $attachment;

        return $this;
    }

    public function getAttachment()
    {
        return $this->data['_attachments'];
    }

	/**
	 * @param array $data [ColumnName => Value]
	 */
	public function populateFromArray(array $data)
	{
		foreach ($data as $column => $value) {
			$this->data[$column] = $value;
		}
	}

	/**
	 * @return array
	 */
	public function getData()
	{
		return $this->data;
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