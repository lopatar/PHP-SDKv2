<?php
declare(strict_types=1);

namespace Sdk\Http\Entities;

/**
 *
 */
enum RequestMethod: string
{
	/**
	 * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Methods/GET
	 * The GET method requests a representation of the specified resource. Requests using GET should only retrieve data.
	 */
	case GET = 'GET';

	/**
	 * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Methods/HEAD
	 * The HEAD method asks for a response identical to a GET request, but without the response body.
	 */
	case HEAD = 'HEAD';

	/**
	 * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Methods/POST
	 * The POST method submits an entity to the specified resource, often causing a change in state or side effects on the server.
	 */
	case POST = 'POST';

	/**
	 * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Methods/PUT
	 * The PUT method replaces all current representations of the target resource with the request payload.
	 */
	case PUT = 'PUT';

	/**
	 * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Methods/DELETE
	 * The DELETE method deletes the specified resource.
	 */
	case DELETE = 'DELETE';

	/**
	 * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Methods/OPTIONS
	 * The OPTIONS method describes the communication options for the target resource.
	 */
	case OPTIONS = 'OPTIONS';

	/**
	 * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Methods/PATCH
	 * The PATCH method applies partial modifications to a resource.
	 */
	case PATCH = 'PATCH';
}