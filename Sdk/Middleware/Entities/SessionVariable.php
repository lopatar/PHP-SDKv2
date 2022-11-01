<?php

namespace Sdk\Middleware\Entities;

/**
 * @internal
 */
enum SessionVariable: string
{
	case CSRF_TOKEN = 'Csrf-Token'; //getallheaders() capitalizes every header name, therefore cannot use camelCase
	case CSRF_EXPIRES = 'csrfExpires';
	case COOKIE_ENCRYPTION_KEY = 'cookieEncKey';
}
