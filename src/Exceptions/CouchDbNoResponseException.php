<?php
namespace CouchDbAdapter\Exceptions;

class CouchDbNoResponseException extends CouchDbException
{
	function __construct()
	{
		parent::__construct(array('status_message' => 'No response from server - '));
	}
}