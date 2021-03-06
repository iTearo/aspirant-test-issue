<?php

declare(strict_types=1);

namespace App\Support;

use Symfony\Component\Yaml\Yaml;

/**
 * Class Config.
 */
class Config
{
    /**
     * @var array
     */
    private $config = [];

    /**
     * Config constructor.
     *
     * @param string $dir
     * @param string $env
     * @param string $root
     */
    public function __construct(string $dir, string $env, string $root)
    {
        if (!is_dir($dir)) {
            throw new \RuntimeException(sprintf('Config directory %s not found', $dir));
        }

        $config = Yaml::parseFile($dir . '/app.yaml');
        $envConfigPath = $dir . '/app.' . $env . '.yaml';
        if (is_readable($envConfigPath)) {
            $config = array_replace_recursive($config, Yaml::parseFile($envConfigPath));
        }

        foreach ($config as $item => $value) {
            $this->config[$item] = $value;
        }

        if (is_readable($dir . '/monolog.yaml')) {
            $this->config = array_merge($this->config, Yaml::parseFile($dir . '/monolog.yaml')['monolog']);
        }

        if (is_readable($dir . '/http-client.yaml')) {
            $this->config = array_merge($this->config, Yaml::parseFile($dir . '/http-client.yaml'));
        }

        if (is_readable($dir . '/doctrine.yaml')) {
            $this->config['doctrine'] = Yaml::parseFile($dir . '/doctrine.yaml');
        }

        $this->config['environment'] = $env;
        $this->config['base_dir'] = $root;

        $this->resolveDirectories($root);
    }

    /**
     * @param string $root
     */
    private function resolveDirectories(string $root): void
    {
        if (!isset($this->config['templates'])) {
            throw new \RuntimeException('\'templates\' parameter in config is required');
        }
        if (!is_array($this->config['templates'])) {
            throw new \RuntimeException('\'templates\' parameter in config must be an array');
        }
        foreach ($this->config['templates'] as $name => $value) {
            if (empty($value)) {
                $this->config['templates'][$name] = null;
                continue;
            }

            if (strpos($value, '/') === 0) {
                continue;
            }

            $this->config['templates'][$name] = $root . '/' . $value;
        }
    }

    /**
     * @param string $name
     *
     * @return mixed|null
     */
    public function get(string $name)
    {
        return $this->config[$name] ?? null;
    }
}
