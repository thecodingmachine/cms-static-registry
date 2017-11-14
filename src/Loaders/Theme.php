<?php
namespace TheCodingMachine\CMS\StaticRegistry\Loaders;

use Symfony\Component\Yaml\Yaml;

class Theme
{
    /**
     * @var string
     */
    private $path;
    /**
     * @var string[]
     */
    private $zones;

    /**
     * @param string[] $zones
     */
    public function __construct(string $path, array $zones)
    {
        $this->path = $path;
        $this->zones = $zones;
    }

    public static function fromDirectory(string $path): self
    {
        if (!is_dir($path)) {
            throw new UnableToLoadFileException('Cannot find directory '.$path);
        }
        $path = rtrim($path, '/\\');
        $configPath = $path.'/config.yml';
        if (!is_file($configPath)) {
            throw new UnableToLoadFileException('Cannot find config.yml in theme '.$path);
        }

        $yaml = Yaml::parse(file_get_contents($configPath));

        $compulsoryFields = ['zones'];

        foreach ($compulsoryFields as $field) {
            if (!isset($yaml[$field])) {
                throw new UnableToLoadFileException('Missing field '.$field.' in YAML file '.$configPath);
            }
        }

        return new self(
            $path,
            $yaml['zones']
        );
    }

    /**
     * Returns true if $path is a theme directory.
     *
     * @param string $path The path, with no trailing /
     * @return bool
     */
    public static function existsInDirectory(string $path): bool
    {
        $configPath = $path.'/config.yml';
        return file_exists($configPath);
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return basename($this->path);
    }

    /**
     * @return string
     */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * @return string[]
     */
    public function getZones(): array
    {
        return $this->zones;
    }
}
