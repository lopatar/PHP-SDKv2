<?php
declare(strict_types=1);

namespace Sdk\Http\Entities;

use JetBrains\PhpStorm\Immutable;
use Sdk\Http\Request;

/**
 * @uses DomainName
 * Helper class that allow gives us all the information about the URL
 */
#[Immutable]
final readonly class Url
{
    /**
     * @var string Url in the format of (scheme://domainNamePathParameters)
     */
    public string $fullText;
    public string $scheme;
    public DomainName $domainName;
    public int $port;
    public string $path;
    /**
     * @var string Always an empty string, anchor not sent to server
     */
    public string $anchor;
    /**
     * @var string[]
     */
    public array $parameters;

    public function __construct(Request $request)
    {
        $this->anchor = '';
        $this->scheme = $request->getServer('REQUEST_SCHEME');

        $serverName = $request->getServer('SERVER_NAME');
        $this->domainName = new DomainName($serverName);
        $this->port = intval($request->getServer('SERVER_PORT'));

        $requestUri = $request->getServer('REQUEST_URI');
        $questionMarkIndex = strpos($requestUri, '?');

        if ($questionMarkIndex === false) {
            $this->path = $requestUri; //no parameters, therefore request uri is the path
            $this->parameters = []; //to avoid property uninitialized
        } else {
            $this->path = substr($requestUri, 0, $questionMarkIndex); //path is everything until the question mark
            $parametersStrings = substr($requestUri, $questionMarkIndex + 1); //parameters are everything behind the ?
            $parameters = explode('&', $parametersStrings); //URL parameters are separated using &

            $tempParameters = [];

            foreach ($parameters as $parameter) {
                //parameters are in format name=value, therefore we have to explode
                $parameterValues = explode('=', $parameter);
                $tempParameters[$parameterValues[0]] = $parameterValues[1];
            }

            $this->parameters = $tempParameters;
        }

        $this->fullText = "$this->scheme://$serverName$requestUri";
    }

    public function hasParameter(string $name): bool
    {
        return isset($this->parameters[$name]);
    }

    public function getParameter(string $name): string|null
    {
        return $this->parameters[$name] ?? null;
    }

    public function isHttps(): bool
    {
        return $this->scheme === 'https';
    }
}