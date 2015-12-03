<?php

namespace CouchDbAdapter\Client;

use CouchDbAdapter\Exceptions\CouchDbException;
use CouchDbAdapter\Exceptions\CouchDbNoResponseException;

class ClientExceptionFactory
{
	// CouchDB response codes
	const COUCH_STATUS_CODES = [
		404 => 'CouchDbNotFoundException',
		403 => 'CouchDbForbiddenException',
		401 => 'CouchDbUnauthorizedException',
		417 => 'CouchDbExpectationException',
		409 => 'CouchDbConflictException'
	];

	/**
	 * @param ResponseInterface $response
	 * @param string $method
	 * @param string $url
	 * @return mixed
	 */
	public static function factory(ResponseInterface $response, $method, $url)
	{
		if (! $response) {
			return new CouchDbNoResponseException();
		}

		if (in_array($response->getStatusCode(), self::COUCH_STATUS_CODES)) {
			$couchDbException = self::COUCH_STATUS_CODES[$response->getStatusCode()];
			return new $couchDbException($response, $method, $url);
		} else {
			return new CouchDbException($response, $method, $url);
		}
	}
}