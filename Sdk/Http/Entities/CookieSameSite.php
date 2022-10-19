<?php

namespace Sdk\Http\Entities;

enum CookieSameSite: string
{
	case NONE = 'None';
	case LAX = 'Lax';
	case STRICT = 'Strict';
}
