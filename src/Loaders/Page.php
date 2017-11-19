<?php
namespace TheCodingMachine\CMS\StaticRegistry\Loaders;

use Mni\FrontYAML\Parser;

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
     * @param string[]|null $menu
     */
    public function __construct(?string $id, string $title, string $content, string $url, string $lang, ?string $website, ?array $menu, ?int $menuOrder, ?string $menuCssClass, ?string $metaTitle, ?string $metaDescription, ?string $theme)
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
            $yaml['theme'] ?? null
        );
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
     * @return int|null
     */
    public function getMenuOrder(): ?int
    {
        return $this->menuOrder;
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
}
