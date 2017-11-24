<?php
namespace TheCodingMachine\CMS\StaticRegistry\Loaders;

use Mni\FrontYAML\Parser;
use Symfony\Component\Yaml\Yaml;

class Page
{
    /**
     * @var string|null
     */
    private $id;
    /**
     * @var string
     */
    private $title;
    /**
     * @var string
     */
    private $content;
    /**
     * @var string
     */
    private $url;
    /**
     * @var string
     */
    private $lang;
    /**
     * @var null|string
     */
    private $website;
    /**
     * @var null|string[]
     */
    private $menu;
    /**
     * @var int|null
     */
    private $menuOrder;
    /**
     * @var null|string
     */
    private $metaTitle;
    /**
     * @var null|string
     */
    private $metaDescription;
    /**
     * @var null|string
     */
    private $theme;
    /**
     * @var null|string
     */
    private $menuCssClass;
    /**
     * @var null|string
     */
    private $template;
    /**
     * @var array
     */
    private $context;
    /**
     * @var string[]
     */
    private $tags;

    /**
     * @param string[]|null $menu
     * @param mixed[] $context
     * @param string[] $tags
     */
    public function __construct(?string $id, string $title, string $content, string $url, string $lang, ?string $website, ?array $menu, ?int $menuOrder, ?string $menuCssClass, ?string $metaTitle, ?string $metaDescription, ?string $theme, ?string $template, array $context = [], array $tags = [])
    {
        $this->id = $id;
        $this->title = $title;
        $this->content = $content;
        $this->url = $url;
        $this->lang = $lang;
        $this->website = $website;
        $this->menu = $menu;
        $this->menuOrder = $menuOrder;
        $this->menuCssClass = $menuCssClass;
        $this->metaTitle = $metaTitle;
        $this->metaDescription = $metaDescription;
        $this->theme = $theme;
        $this->template = $template;
        $this->context = $context;
        $this->tags = $tags;
    }

    public static function fromFile(string $file): self
    {
        if (!is_readable($file)) {
            throw new UnableToLoadFileException('Cannot read file '.$file);
        }

        $extension = strtolower(pathinfo($file, PATHINFO_EXTENSION));

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

        $document = $parser->parse(file_get_contents($file), $parseMarkDown);

        $yaml = $document->getYAML();

        if (isset($yaml['inherits'])) {
            $baseYaml = self::loadBaseYamlFile(dirname($file).'/'.$yaml['inherits']);
            $yaml = self::mergeYaml($baseYaml, $yaml, dirname($file).'/'.$yaml['inherits']);
        }

        $compulsoryFields = ['title', 'url', 'lang'];

        foreach ($compulsoryFields as $field) {
            if (!isset($yaml[$field])) {
                throw new UnableToLoadFileException('Missing field '.$field.' in YAML front matter of file '.$file);
            }
        }

        return new self(
            $yaml['id'] ?? null,
            $yaml['title'],
            $document->getContent(),
            '/'.ltrim($yaml['url'], '/'),
            $yaml['lang'],
            $yaml['website'] ?? null,
            isset($yaml['menu']) ? array_map('trim', explode('/', $yaml['menu'])) : null,
            $yaml['menu_order'] ?? null,
            $yaml['menu_css_class'] ?? null,
            $yaml['meta_title'] ?? null,
            $yaml['meta_description'] ?? null,
            $yaml['theme'] ?? null,
            $yaml['template'] ?? null,
            $yaml['context'] ?? [],
            $yaml['tags'] ?? []
        );
    }

    /**
     * @return mixed[]
     * @throws UnableToLoadFileException
     */
    private static function loadBaseYamlFile(string $path): array
    {
        if (!is_readable($path)) {
            throw new UnableToLoadFileException('Cannot read base page '.$path.' (used in "inherits" option)');
        }

        return Yaml::parse(file_get_contents($path));
    }

    /**
     * @param mixed[] $baseYaml
     * @param mixed[] $yaml
     * @param string $file
     * @return mixed[]
     */
    private static function mergeYaml(array $baseYaml, array $yaml, string $file): array
    {
        if (isset($baseYaml['inherits'])) {
            $baseYaml2 = self::loadBaseYamlFile(dirname($file).'/'.$baseYaml['inherits']);
            $baseYaml = self::mergeYaml($baseYaml2, $baseYaml, dirname($file).'/'.$baseYaml['inherits']);
        }

        $arrayMerger = new YamlUtils();
        return $arrayMerger->mergeArrays($baseYaml, $yaml, [
            'title' => YamlUtils::OVERRIDE,
            'lang' => YamlUtils::OVERRIDE,
            'website' => YamlUtils::OVERRIDE,
            'menu_css_class' => YamlUtils::OVERRIDE,
            'meta_title' => YamlUtils::OVERRIDE,
            'meta_description' => YamlUtils::OVERRIDE,
            'theme' => YamlUtils::OVERRIDE,
            'template' => YamlUtils::OVERRIDE,
            'context' => YamlUtils::MERGE_ARRAY,
            'tags' => YamlUtils::MERGE_ARRAY,
        ]);
    }

    /**
     * @return null|string
     */
    public function getId(): ?string
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
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
    public function getUrl(): string
    {
        return $this->url;
    }

    /**
     * @return string
     */
    public function getLang(): string
    {
        return $this->lang;
    }

    /**
     * @return string|null
     */
    public function getWebsite(): ?string
    {
        return $this->website;
    }

    /**
     * @return null|string[]
     */
    public function getMenu(): ?array
    {
        return $this->menu;
    }

    /**
     * @return int
     */
    public function getMenuOrder(): int
    {
        return $this->menuOrder ?? 0;
    }

    /**
     * @return null|string
     */
    public function getMenuCssClass(): ?string
    {
        return $this->menuCssClass;
    }

    /**
     * @return null|string
     */
    public function getMetaTitle(): ?string
    {
        return $this->metaTitle;
    }

    /**
     * @return null|string
     */
    public function getMetaDescription(): ?string
    {
        return $this->metaDescription;
    }

    /**
     * @return null|string
     */
    public function getTheme(): ?string
    {
        return $this->theme;
    }

    /**
     * @return null|string
     */
    public function getTemplate(): ?string
    {
        return $this->template;
    }

    /**
     * @return mixed[]
     */
    public function getContext(): array
    {
        return $this->context;
    }

    /**
     * @return string[]
     */
    public function getTags(): array
    {
        return $this->tags;
    }
}
