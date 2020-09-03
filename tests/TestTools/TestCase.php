<?php

declare(strict_types=1);

namespace TestTools;

use Psr\Container\ContainerInterface;

class TestCase extends \PHPUnit\Framework\TestCase
{
    private static ContainerInterface $container;

    protected static function getContainer(): ContainerInterface
    {
        return static::$container;
    }

    public static function setContainer(ContainerInterface $container): void
    {
        static::$container = $container;
    }
}
