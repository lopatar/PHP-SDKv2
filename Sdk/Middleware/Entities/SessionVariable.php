<?php

namespace Sdk\Middleware\Entities;

enum SessionVariable: string
{
    case CSRF_TOKEN = 'csrfToken';
    case CSRF_EXPIRES = 'csrfExpires';
    case COOKIE_ENCRYPTION_KEY = 'cookieEncKey';
}
