<?php

namespace App\Support;

use RuntimeException;
use PHPUnit\Framework\TestCase;

class ConfigTest extends TestCase
{
    public function test__construct(): void
    {
        // Arrange
        $rootPath = $this->getRootPath();

        // Act
        $config = new Config($rootPath . '/_tests_data', 'test', $rootPath);

        // Assert
        self::assertInstanceOf(Config::class, $config);
    }

    public function test__constructError(): void
    {
        // Assert
        $this->expectException(RuntimeException::class);

        // Act
        new Config('not-exists', 'test', 'not-exists-too');
    }

    public function testEnvironmentFileLoad(): void
    {
        // Arrange
        $rootPath = $this->getRootPath();

        // Act
        $config = new Config($rootPath . '/_tests_data', 'test', $rootPath);

        // Assert
        self::assertNull($config->get('templates')['cache']);
    }

    public function testGet(): void
    {
        // Arrange
        $rootPath = $this->getRootPath();

        // Act
        $config = new Config($rootPath . '/_tests_data', 'test', $rootPath);

        // Assert
        self::assertNotEmpty($config->get('slim'));
        self::assertNotEmpty($config->get('templates'));
        self::assertEquals($rootPath . '/template', $config->get('templates')['dir']);
    }

    protected function getRootPath(): string
    {
        return dirname(__DIR__, 2);
    }
}
