<?php

namespace App\Support;

use Illuminate\Contracts\Translation\Loader;

class SiteContentFileLoader implements Loader
{
    public function __construct(private Loader $loader) {}

    /**
     * @return array<string, mixed>
     */
    public function load($locale, $group, $namespace = null): array
    {
        $lines = $this->loader->load($locale, $group, $namespace);

        if (($namespace === '*' || $namespace === null) && $group === 'store') {
            return SiteContentLoader::mergeIntoStore($lines, (string) $locale);
        }

        return $lines;
    }

    public function addNamespace($namespace, $hint): void
    {
        $this->loader->addNamespace($namespace, $hint);
    }

    public function addJsonPath($path): void
    {
        $this->loader->addJsonPath($path);
    }

    public function addPath($path): void
    {
        if (method_exists($this->loader, 'addPath')) {
            $this->loader->addPath($path);
        }
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function namespaces(): array
    {
        return $this->loader->namespaces();
    }
}
