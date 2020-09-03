<?php

declare(strict_types=1);

namespace App\Provider;

use App\Controller\MovieController;
use Domain\Movie\MovieRepository;
use App\Support\Config;
use App\Support\ServiceProviderInterface;
use Slim\Interfaces\RouteCollectorInterface;
use Slim\Interfaces\RouteCollectorProxyInterface;
use Symfony\Component\Yaml\Yaml;
use Twig\Environment;
use UltraLite\Container\Container;

class WebProvider implements ServiceProviderInterface
{
    public function register(Container $container): void
    {
        $this->defineControllerDi($container);
        $this->defineRoutes($container);
    }

    protected function defineControllerDi(Container $container): void
    {
        $container->set(MovieController::class, fn() => new MovieController(
            $container->get(RouteCollectorInterface::class),
            $container->get(Environment::class),
            $container->get(MovieRepository::class))
        );
    }

    protected function defineRoutes(Container $container): void
    {
        /** @var RouteCollectorInterface $router */
        $router = $container->get(RouteCollectorInterface::class);

        $router->group('', function (RouteCollectorProxyInterface $router) use ($container) {
            $routes = self::getRoutes($container);
            foreach ($routes as $routeName => $routeConfig) {
                $router->{$routeConfig['method']}($routeConfig['path'] ?? '', $routeConfig['controller'] . ':' . $routeConfig['action'])
                    ->setName($routeName);
            }
        });
    }

    protected static function getRoutes(Container $container): array
    {
        return Yaml::parseFile($container->get(Config::class)->get('base_dir') . '/config/routes.yaml');
    }
}
