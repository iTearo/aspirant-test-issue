<?php

declare(strict_types=1);

namespace App\Controller;

use App\Repository\Movie\MovieRepository;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Exception\HttpBadRequestException;
use Slim\Interfaces\RouteCollectorInterface;
use Twig\Environment;

class MovieController
{
    private RouteCollectorInterface $routeCollector;

    private Environment $twig;

    private MovieRepository $movieRepository;

    public function __construct(RouteCollectorInterface $routeCollector, Environment $twig, MovieRepository $movieRepository)
    {
        $this->routeCollector = $routeCollector;
        $this->twig = $twig;
        $this->movieRepository = $movieRepository;
    }

    public function listAction(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        try {
            $data = $this->twig->render('movie/list.html.twig', [
                'movies' => $this->movieRepository->getAll(),
            ]);

        } catch (\Exception $e) {
            throw new HttpBadRequestException($request, $e->getMessage(), $e);
        }

        $response->getBody()->write($data);

        return $response;
    }
}
