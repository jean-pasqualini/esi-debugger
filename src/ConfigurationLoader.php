<?php
use Symfony\Component\OptionsResolver\OptionsResolver;
use Http\Cache\Symfony;
use Http\Cache\Iframe;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Yaml\Yaml;

/**
 * Configuration
 *
 * @author Jean Pasqualini <jpasqualini75@gmail.com>
 */
class ConfigurationLoader
{
    protected $path;

    public function __construct($path)
    {
        $this->path = $path;
    }

    public function load($file)
    {
        $locator = new FileLocator($this->path);
        $file = $locator->locate($file, null, true);
        return Yaml::parse(file_get_contents($file));
    }
}