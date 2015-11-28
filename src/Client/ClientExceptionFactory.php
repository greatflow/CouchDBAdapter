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
	 * @param $response
	 * @param $method
	 * @param $url
	 *
	 * @return mixed
	 */
	public static function factory($response, $method, $url)
	{
		if (! $response) {
			return new CouchDbNoResponseException();
		}

		if (isset($response['statusCode']) && in_array($response['statusCode'], self::COUCH_STATUS_CODES)) {
			$couchDbException = self::COUCH_STATUS_CODES[$response['statusCode']];
			return new $couchDbException($response, $method, $url);
		} else {
			return new CouchDbException($response, $method, $url);
		}
	}
}