<?php


namespace TheCodingMachine\CMS\StaticRegistry\Registry;


class BlockNotFoundException extends \Exception
{
    public static function couldNotLoadBlock(string $blockName): self
    {
        return new self(sprintf('Could not find block with ID "%s". Block file with ID %s does not exist, and could not find an entry in the container whose name is %s', $blockName, $blockName, $blockName));
    }
}
