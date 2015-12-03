<?php
namespace CouchDbAdapter\Exceptions;

use CouchDbAdapter\Client\ResponseInterface;
use Exception;

/**
 * Customized Exception class for CouchDB errors
 *
 * this class uses : the Exception message to store the HTTP message sent by the server
 * the Exception code to store the HTTP status code sent by the server
 * and adds a method getBody() to fetch the body sent by the server (if any)
 *
 */
class CouchDbException extends Exception
{
	/**
	 * @param ResponseInterface $response
	 * @param string $method
	 * @param string $url
	 */
	public function __construct(ResponseInterface $response, $method, $url)
	{
		if (! empty($response->getBody())) {
			$message = $response->getBody();
		}

		if (isset($message)) {
			$message .= " ($method $url)";
		} else {
			$message = "$method $url";
		}

		parent::__construct($message, $response->getStatusCode());
	}
}