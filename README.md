# PHP-SDKv2

Powerful PHP framework, written in PHP 8.1, modernised version of rather
primitive [PHP SDK](https://github.com/lopatar/PHP-SDK) (which is now archived).

# Requirements

- PHP 8.1
- Composer
- Web server (routing all requests to public/index.php)

# Installation

To install the SDK, please run the following composer command

```shell
composer require "lopatar/php-sdkv2" <PROJECT-NAME>
```

A folder called PROJECT-NAME will be created, please point your web server to /path/to/<PROJECT-NAME>/public/index.php

# Routing requests to index.php

- NGINX

```
    root /path/to/public;
    index index.php;

    location / {
        try_files $uri /index.php$is_args$args =404;
    }
```

# Features

- [Request](https://github.com/lopatar/PHP-SDKv2/blob/main/Sdk/Http/Request.php) object
    - [URL](https://github.com/lopatar/PHP-SDKv2/blob/main/Sdk/Http/Entities/Url.php) management
    - [Cookie](https://github.com/lopatar/PHP-SDKv2/blob/main/Sdk/Http/Entities/Cookie.php) management
        - Cookies can be automatically encrypted & decrypted
          using [AES-256-CBC](https://github.com/lopatar/PHP-SDKv2/blob/main/App/Config.php#L68)
    - Headers, GET, POST, SERVER variables management
- [Response](https://github.com/lopatar/PHP-SDKv2/blob/main/Sdk/Http/Response.php) object
    - [View](https://github.com/lopatar/PHP-SDKv2/blob/main/Sdk/Render/View.php) system (injecting PHP variables into
      HTML code)
    - Status code, body (writing, flushing) management
- Routing
    - Anonymous callbacks / ControllerName::methodName
    - URL parameters (type validation, min & max value (length for strings), escaping)
- Middleware (can be used on
  specific [Routes](https://github.com/lopatar/PHP-SDKv2/blob/main/Sdk/Routing/Entities/Route.php) or
  the [App](https://github.com/lopatar/PHP-SDKv2/blob/main/Sdk/App.php) object)
    - [IMiddleware](https://github.com/lopatar/PHP-SDKv2/blob/main/Sdk/Middleware/Interfaces/IMiddleware.php)
      interface (used to define your own middleware)
    - [Session](https://github.com/lopatar/PHP-SDKv2/blob/main/Sdk/Middleware/Session.php) middleware, used storing data
      across requests
    - [CSRF](https://github.com/lopatar/PHP-SDKv2/blob/main/Sdk/Middleware/CSRF.php) middleware, used to protect against
      CSRF attacks
- Database connectors
    - [MySQL/MariaDB](https://github.com/lopatar/PHP-SDKv2/blob/main/Sdk/Database/MariaDB/Connection.php) connector,
      configured via the [Config](https://github.com/lopatar/PHP-SDKv2/blob/main/App/Config.php) system
- [Utilities](https://github.com/lopatar/PHP-SDKv2/blob/main/Sdk/Utils/) namespace
    - [Encryption](https://github.com/lopatar/PHP-SDKv2/blob/main/Sdk/Utils/Encryption) namespace
        - [AES256-CBC](https://github.com/lopatar/PHP-SDKv2/blob/main/Sdk/Utils/Encryption/AES256.php) class
        - [Random](https://github.com/lopatar/PHP-SDKv2/blob/main/Sdk/Utils/Random.php) class, used for generating
          random **crypto safe** & non-safe values
- [Config](https://github.com/lopatar/PHP-SDKv2/blob/main/App/Config.php) object
    - Used for configuring database connectors, session & CSRF middleware
    - Cookie encryption toggle
    - [Server header spoofing](https://github.com/lopatar/PHP-SDKv2/blob/main/App/Config.php#L61) feature, can be used
      to hide your web server software