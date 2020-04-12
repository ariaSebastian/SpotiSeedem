<?php


namespace App\Service;


use Exception;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class SpotifyService
{
    // Key para el token en sesion
    const TOKEN_SESSION_KEY = 'RBSsjgB7jLNywit677yrGRSNbw5r7gLw';
    // URI para obtener token
    const TOKE_URI = 'https://accounts.spotify.com/api/token';
    // Base URI para las peticiones
    const BASE_URI = 'https://api.spotify.com/v1/';
    // Número de intentos para las peticiones
    const ATTEMPTS_NUMBER = 3;

    /**
     * @var ContainerInterface
     */
    public $container;

    /**
     * @var Session
     */
    public $session;

    /**
     * @var LoggerInterface
     */
    public $logger;

    /**
     * @var HttpClientInterface
     */
    public $client;

    /**
     * Indica si el token ya esta inicializado
     *
     * @var bool
     */
    public $isInitialized;

    public function __construct(ContainerInterface $container, LoggerInterface $logger)
    {
        $this->container = $container;
        $this->session = $this->container->get('session');
        $this->logger = $logger;
        $this->client = HttpClient::createForBaseUri(self::BASE_URI, [
            'auth_bearer' => $this->session->get(self::TOKEN_SESSION_KEY),
        ]);

//        if (!$this->session->has(self::TOKEN_SESSION_KEY)) {
            $this->isInitialized = $this->tokenInitialize();
//        } else {
//            $this->isInitialized = true;
//        }
    }


    /**
     * Inicializa el access_token para el resto de peticiones
     *
     * @return bool
     */
    public function tokenInitialize()
    {
        $this->session->remove(self::TOKEN_SESSION_KEY);

        try {
            $attempts = 0;
            while (!$this->session->has(self::TOKEN_SESSION_KEY) && $attempts < self::ATTEMPTS_NUMBER) {
                $response = $this->client->request(Request::METHOD_POST, self::TOKE_URI, [
                    'auth_basic' => [$_ENV['SPOTIFY_CLIENT_ID'], $_ENV['SPOTIFY_CLIENT_SECRET']],
                    'body' => [
                        'grant_type' => 'client_credentials',
                    ],
                ]);

                if ($response->getStatusCode() == Response::HTTP_OK) {
                    $response = (object)$response->toArray();
                    $this->session->set(self::TOKEN_SESSION_KEY, $response->access_token);
                    return true;
                } else {
                    $this->logger->warning("SpotifyService.tokenInitialize: {status_code: {$response->getStatusCode()}} | {$response->getContent(false)}");
                }

                $attempts++;
            }
        } catch (Exception $ex) {
            $this->logger->error("SpotifyService.tokenInitialize: {$ex->getMessage()}");
        }

        return false;
    }

    /**
     * Retorna los nuevos lanzamientos
     *
     * @param int $offset
     * @return bool|array
     */
    public function getNewReleases($offset = 0)
    {
        if ($this->isInitialized) {
            try {
                $attempts = 0;
                while ($attempts < self::ATTEMPTS_NUMBER) {
                    $response = $this->client->request(Request::METHOD_GET, 'browse/new-releases', [
                        'auth_bearer' => $this->session->get(self::TOKEN_SESSION_KEY),
                        'query' => [
                            'country' => 'CO',
                            'offset' => $offset,
                        ],
                    ]);

                    if ($response->getStatusCode() == Response::HTTP_OK) {
                        return $response->toArray()['albums'];
                    } elseif ($response->getStatusCode() == Response::HTTP_UNAUTHORIZED) {
                        $this->tokenInitialize();
                    }

                    $attempts++;
                }
            } catch (Exception $ex) {
                $this->logger->error("SpotifyService.getNewReleases: {$ex->getMessage()}");
            }
        }

        return false;
    }

    /**
     * Retorna la información de un artista por medio de su ID
     *
     * @param $id
     * @return bool|object
     */
    public function getArtist($id)
    {
        if ($this->isInitialized) {
            try {
                $attempts = 0;
                while ($attempts < self::ATTEMPTS_NUMBER) {
                    $response = $this->client->request(Request::METHOD_GET, "artists/$id", [
                        'auth_bearer' => $this->session->get(self::TOKEN_SESSION_KEY),
                    ]);

                    if ($response->getStatusCode() == Response::HTTP_OK) {
                        return (object)$response->toArray();
                    } elseif ($response->getStatusCode() == Response::HTTP_UNAUTHORIZED) {
                        $this->tokenInitialize();
                    }

                    $attempts++;
                }
            } catch (Exception $ex) {
                $this->logger->error("SpotifyService.getArtist: {$ex->getMessage()}");
            }
        }

        return false;
    }

    /**
     * Retorna el top de canciones de un artista por medio de su ID
     *
     * @param $id
     * @return bool|array
     */
    public function getArtistsTopTracks($id)
    {
        if ($this->isInitialized) {
            try {
                $attempts = 0;
                while ($attempts < self::ATTEMPTS_NUMBER) {
                    $response = $this->client->request(Request::METHOD_GET, "artists/$id/top-tracks", [
                        'auth_bearer' => $this->session->get(self::TOKEN_SESSION_KEY),
                        'query' => [
                            'country' => 'CO',
                        ],
                    ]);

                    if ($response->getStatusCode() == Response::HTTP_OK) {
                        return $response->toArray()['tracks'];
                    } elseif ($response->getStatusCode() == Response::HTTP_UNAUTHORIZED) {
                        $this->tokenInitialize();
                    }

                    $attempts++;
                }
            } catch (Exception $ex) {
                $this->logger->error("SpotifyService.getArtistsTopTracks: {$ex->getMessage()}");
            }
        }

        return false;
    }
}