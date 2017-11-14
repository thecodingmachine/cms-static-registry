<?php


namespace TheCodingMachine\CMS\StaticRegistry\Registry;


class PageNotFoundException extends \Exception
{
    public static function couldNotFindPage(string $url, string $domain): self
    {
        throw new self(sprintf('Could not find page for URL %s of domain %s', $url, $domain));
    }
}
