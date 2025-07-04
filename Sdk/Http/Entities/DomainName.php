<?php
declare(strict_types=1);

namespace Sdk\Http\Entities;

/**
 * Helper class that gives us all the possible information regarding a domain name
 */
final readonly class DomainName
{
    /**
     * @var string domain name with subdomains and TLD
     */
    public string $fullText;

    /**
     * @var string domain name including TLD
     */
    public string $domain;

    /**
     * @var string top level domain name (.com, .net etc..)
     */
    public string $tld;

    /**
     * @var string[] $subDomains
     * Subdomains indexed in this way (test.url.example.com) 0 = url, 1 = test
     */
    public array $subDomains;


    /**
     * @param string $serverName Domain text in format (www.test.example.com)
     */
    public function __construct(string $serverName)
    {
        $nameParts = explode('.', $serverName);
        $count = count($nameParts);

        if ($count === 0) {
            $this->domain = $serverName;
            $this->fullText = $serverName;
            $this->subDomains = [];
            return;
        }

        $this->tld = $nameParts[$count - 1]; //TLD is the last
        $this->domain = $nameParts[$count - 2] . ".$this->tld";

        $subDomains = [];

        if ($count > 2) {
            //there are subdomains present
            for ($i = $count - 3; $i >= 0; $i--) {
                $subDomains[] = $nameParts[$i];
            }
        }

        $this->subDomains = $subDomains;
        $this->fullText = $serverName; //To avoid property uninitialized
    }
}