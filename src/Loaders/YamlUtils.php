<?php


namespace TheCodingMachine\CMS\StaticRegistry\Loaders;


class YamlUtils
{
    public const OVERRIDE = 'override';
    public const MERGE_ARRAY = 'mergeArray';

    /**
     * @param mixed[] $baseYaml
     * @param mixed[] $yaml
     * @param string[] $instructions
     * @return mixed[]
     */
    public function mergeArrays(array $baseYaml, array $yaml, array $instructions): array
    {
        foreach ($instructions as $key => $value) {
            switch ($value) {
                case self::OVERRIDE:
                    if (!isset($yaml[$key]) && isset($baseYaml[$key])) {
                        $yaml[$key] = $baseYaml[$key];
                    }
                    break;
                case self::MERGE_ARRAY:
                    if (isset($baseYaml[$key])) {
                        $yaml[$key] = $this->recursiveMerge($baseYaml[$key], $yaml[$key]);
                    }
                    break;
                default:
                    throw new \InvalidArgumentException('Unexpected instruction "'.$value.'" passed. Expecting either "override" or "mergeArray".');
            }
        }

        return $yaml;
    }

    private function recursiveMerge(array $base, array $target): array
    {
        if (!$this->isAssoc($base) && !$this->isAssoc($target)) {
            foreach ($target as $value) {
                $base[] = $value;
            }
            return $base;
        }

        foreach ($base as $key => $value) {
            if (!isset($target[$key])) {
                $target[$key] = $value;
                continue;
            }
            if (is_array($target[$key]) && is_array($value)) {
                $target[$key] = $this->recursiveMerge($value, $target[$key]);
            }
            // Otherwise, target wins, nothing to do
        }
        return $target;
    }

    /**
     * @param mixed[] $arr
     * @return bool
     */
    private function isAssoc(array $arr): bool
    {
        if (array() === $arr) return false;
        return array_keys($arr) !== range(0, count($arr) - 1);
    }
}
