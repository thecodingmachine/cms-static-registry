<?php
namespace TheCodingMachine\CMS\StaticRegistry\Loaders;

use Barclays\Model\BaseBlock;
use Mni\FrontYAML\Parser;
use Symfony\Component\Yaml\Yaml;
use TheCodingMachine\CMS\Block\Block;

class SubTheme
{
    /**
     * @var string
     */
    private $name;
    /**
     * @var string
     */
    private $parent;
    /**
     * @var array|string[][]
     */
    private $assignations;

    /**
     * @param string $name
     * @param string $parent
     * @param string[][] $assignations
     */
    public function __construct(string $name, string $parent, array $assignations)
    {
        $this->name = $name;
        $this->parent = $parent;
        $this->assignations = $assignations;
    }

    public static function fromFile(string $file): self
    {
        if (!is_readable($file)) {
            throw new UnableToLoadFileException('Cannot read file '.$file);
        }

        $yaml = Yaml::parse(file_get_contents($file));

        $compulsoryFields = ['name', 'parent', 'assignations'];

        foreach ($compulsoryFields as $field) {
            if (!isset($yaml[$field])) {
                throw new UnableToLoadFileException('Missing field "'.$field.'" in YAML file '.$file);
            }
        }

        return new self(
            $yaml['name'],
            $yaml['parent'],
            $yaml['assignations']
        );
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getParent(): string
    {
        return $this->parent;
    }

    /**
     * @return array|string[][]
     */
    public function getAssignations()
    {
        return $this->assignations;
    }
}
