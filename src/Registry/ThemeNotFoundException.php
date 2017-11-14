<?php


namespace TheCodingMachine\CMS\StaticRegistry\Registry;


class ThemeNotFoundException extends \Exception
{
    public static function couldNotLoadTheme(string $themeName, string $dir): self
    {
        return new self(sprintf('Could not find theme "%s". Could not find the theme in %s, and could not find an entry in the container whose name is %s', $themeName, $dir, $themeName));
    }
}