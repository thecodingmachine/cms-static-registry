<?php


namespace TheCodingMachine\CMS\StaticRegistry\Registry;


use Psr\Container\ContainerInterface;
use Psr\SimpleCache\CacheInterface;
use Symfony\Component\Finder\Finder;
use TheCodingMachine\CMS\StaticRegistry\Loaders\SubTheme;
use TheCodingMachine\CMS\StaticRegistry\Loaders\Theme;
use TheCodingMachine\CMS\Theme\SubThemeDescriptor;
use TheCodingMachine\CMS\Theme\ThemeDescriptorInterface;
use TheCodingMachine\CMS\Theme\TwigThemeDescriptor;

/**
 * The theme registry can fetch Theme objects from the "theme" directory or from the container.
 */
class ThemeRegistry
{
    /**
     * @var string
     */
    private $themeDirectory;
    /**
     * @var string
     */
    private $subThemeDirectory;
    /**
     * @var ContainerInterface
     */
    private $container;
    /**
     * @var CacheInterface
     */
    private $cache;
    /**
     * @var SubTheme[]
     */
    private $subThemes;
    /**
     * @var BlockRegistry
     */
    private $blockRegistry;

    public function __construct(string $themeDirectory, string $subThemeDirectory, ContainerInterface $container, CacheInterface $cache, BlockRegistry $blockRegistry)
    {
        $this->themeDirectory = rtrim($themeDirectory, '/\\').'/';
        $this->subThemeDirectory = rtrim($subThemeDirectory, '/\\').'/';
        $this->container = $container;
        $this->cache = $cache;
        $this->blockRegistry = $blockRegistry;
    }

    public function getThemeDescriptor(string $themeName): ThemeDescriptorInterface
    {
        $key = 'theme.'.$themeName;
        $theme = $this->cache->get($key);
        if ($theme === null) {
            $theme = $this->getThemeNoCache($themeName);
            $this->cache->set($key, $theme);
        }
        return $theme;
    }

    private function getThemeNoCache(string $themeName): ThemeDescriptorInterface
    {
        $dir = $this->themeDirectory.$themeName;
        if ($this->container->has($themeName)) {
            $entry = $this->container->get($themeName);
            if ($entry instanceof Theme) {
                return $this->themeToBlock($entry);
            }
            if ($entry instanceof SubTheme) {
                return $this->subThemeToBlock($entry);
            }
            if ($entry instanceof ThemeDescriptorInterface) {
                return $entry;
            }
        }

        if (Theme::existsInDirectory($dir)) {
            return $this->themeToBlock(Theme::fromDirectory($dir));
        }

        $subThemes = $this->getSubThemes();
        if (isset($subThemes[$themeName])) {
            return $this->subThemeToBlock($subThemes[$themeName]);
        } else {
            throw ThemeNotFoundException::couldNotLoadTheme($themeName, $dir);
        }
    }

    private function themeToBlock(Theme $theme) : ThemeDescriptorInterface
    {
        return new TwigThemeDescriptor('index.twig', [
            'theme' => $theme->getName()
        ]);
    }

    private function subThemeToBlock(SubTheme $subTheme) : ThemeDescriptorInterface
    {
        $context = [];
        $themePath = $this->getThemePath($subTheme);
        foreach ($subTheme->getAssignations() as $zone => $blocks) {
            foreach ($blocks as $blockName) {
                foreach ($this->blockRegistry->getBlocks($blockName) as $block) {
                    $context[$zone][] = $block->toCmsBlock($themePath);
                }
            }
        }
        return new SubThemeDescriptor($this->getThemeDescriptor($subTheme->getParent()), $context);
    }

    private function getThemePath(SubTheme $subTheme): string
    {
        $subThemes = $this->getSubThemes();
        $parentName = $subTheme->getParent();
        while (isset($subThemes[$parentName])) {
            $subTheme = $subThemes[$parentName];
            $parentName = $subTheme->getParent();
        }
        return $parentName;
    }

    /**
     * Loads all subthemes and returns them, indexed by name.
     *
     * @return SubTheme[]
     * @throws \TheCodingMachine\CMS\StaticRegistry\Registry\DuplicateSubThemeException
     */
    private function getSubThemes(): array
    {
        if ($this->subThemes === null)
        {
            $this->subThemes = [];
            $fileList = new Finder();

            $fileList->files()->in($this->subThemeDirectory)->name('/\.yml|\.yaml/')->sortByName();

            foreach ($fileList as $splFileInfo) {
                $subTheme = SubTheme::fromFile($splFileInfo->getRealPath());
                $themeName = $subTheme->getName();
                if (isset($this->subThemes[$themeName])) {
                    throw new DuplicateSubThemeException(sprintf('The sub-theme "%s" has been found twice.', $themeName));
                }
                $this->subThemes[$themeName] = $subTheme;
            }
        }
        return $this->subThemes;
    }
}
