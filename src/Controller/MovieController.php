<?php

declare(strict_types=1);

namespace App\Controller;

use App\Repository\Movie\MovieRepository;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Exception\HttpBadRequestException;
use Slim\Exception\HttpNotFoundException;
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
                'router' => $this->routeCollector->getRouteParser(),
            ]);

            $response->getBody()->write($data);

        } catch (\Exception $e) {
            throw new HttpBadRequestException($request, $e->getMessage(), $e);
        }

        return $response;
    }

    public function viewAction(ServerRequestInterface $request, ResponseInterface $response, $params): ResponseInterface
    {
        try {
            $movie = $this->movieRepository->getById((int) $params['movieId']);

            if ($movie === null) {
                throw new HttpNotFoundException($request, 'Movie not found');
            }

            $data = $this->twig->render('movie/view.html.twig', [
                'movie' => $movie,
                'router' => $this->routeCollector->getRouteParser(),
            ]);

            $response->getBody()->write($data);

        } catch (HttpNotFoundException $e) {
            $response->withStatus(404)
                ->getBody()->write($e->getMessage())
            ;

        } catch (\Exception $e) {
            throw new HttpBadRequestException($request, $e->getMessage(), $e);
        }

        return $response;
    }
}
