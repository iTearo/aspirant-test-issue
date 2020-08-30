<?php

declare(strict_types=1);

namespace App\Provider;

use App\Command\FetchDataCommand;
use App\Command\RouteListCommand;
use App\Support\CommandMap;
use App\Support\ServiceProviderInterface;
use Doctrine\ORM\EntityManagerInterface;
use Phinx\Console\Command\Breakpoint;
use Phinx\Console\Command\Create;
use Phinx\Console\Command\Migrate;
use Phinx\Console\Command\Rollback;
use Phinx\Console\Command\SeedCreate;
use Phinx\Console\Command\SeedRun;
use Phinx\Console\Command\Status;
use Psr\Container\ContainerInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Log\LoggerInterface;
use Slim\Interfaces\RouteCollectorInterface;
use Symfony\Component\Console\Command\Command;
use UltraLite\Container\Container;

class ConsoleCommandProvider implements ServiceProviderInterface
{
    public function register(Container $container): void
    {
        $container->set(RouteListCommand::class, static function (ContainerInterface $container) {
            return new RouteListCommand($container->get(RouteCollectorInterface::class));
        });

        $container->set(FetchDataCommand::class, static function (ContainerInterface $container) {
            return new FetchDataCommand($container->get(ClientInterface::class), $container->get(LoggerInterface::class), $container->get(EntityManagerInterface::class));
        });

        $container->get(CommandMap::class)->set(RouteListCommand::getDefaultName(), RouteListCommand::class);
        $container->get(CommandMap::class)->set(FetchDataCommand::getDefaultName(), FetchDataCommand::class);

        $this->registerPhinxCommands($container, 'phinx:');
    }

    private function registerPhinxCommands(Container $container, string $phinxPrefix): void
    {
        $commands = [
            Create::class,
            Migrate::class,
            Rollback::class,
            Status::class,
            Breakpoint::class,
            SeedCreate::class,
            SeedRun::class,
        ];

        foreach ($commands as $commandClassName) {
            /** @var Command|string $commandClassName */
            $commandDefaultName = $commandClassName::getDefaultName();

            $container->set($commandClassName, fn() => new $commandClassName($phinxPrefix . $commandDefaultName));
            $container->get(CommandMap::class)->set($phinxPrefix . $commandDefaultName, $commandClassName);
        }
    }
}
