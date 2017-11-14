<?php
namespace TheCodingMachine\CMS\StaticRegistry\Loaders;

use Mni\FrontYAML\Parser;
use \SplFileInfo;

class Block
{

    /**
     * @var string
     */
    private $id;
    /**
     * @var string
     */
    private $content;
    /**
     * @var string
     */
    private $lang;
    /**
     * @var array|string[]
     */
    private $tags;

    /**
     * @param string[] $tags
     */
    public function __construct(string $id, string $content, string $lang, array $tags)
    {
        $this->id = $id;
        $this->content = $content;
        $this->lang = $lang;
        $this->tags = $tags;
    }

    public static function fromFile(SplFileInfo $file): self
    {
        if (!is_readable($file->getRealPath())) {
            throw new UnableToLoadFileException('Cannot read file '.$file);
        }

        $extension = strtolower($file->getExtension());

        switch ($extension) {
            case 'md':
                $parseMarkDown = true;
                break;
            case 'html':
                $parseMarkDown = false;
                break;
            default:
                throw new InvalidExtensionException(sprintf('Invalid extension for block %s. Valid extensions are .md and .html', $file));
        }

        $parser = new Parser();

        $document = $parser->parse(file_get_contents($file->getRealPath()), $parseMarkDown);

        $yaml = $document->getYAML();

        $compulsoryFields = ['id', 'lang'];

        foreach ($compulsoryFields as $field) {
            if (!isset($yaml[$field])) {
                throw new UnableToLoadFileException('Missing field '.$field.' in YAML front matter of file '.$file);
            }
        }

        return new self(
            $yaml['id'],
            $document->getContent(),
            $yaml['lang'],
            $yaml['tags'] ?? []
        );
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getContent(): string
    {
        return $this->content;
    }

    /**
     * @return string
     */
    public function getLang(): string
    {
        return $this->lang;
    }

    /**
     * @return array|string[]
     */
    public function getTags()
    {
        return $this->tags;
    }
}
