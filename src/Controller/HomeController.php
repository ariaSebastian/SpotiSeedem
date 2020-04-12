<?php

namespace App\Controller;

use App\Service\SpotifyService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class HomeController
 * @package App\Controller
 * @Route("/")
 */
class HomeController extends AbstractController
{

    const ERROR_CONNECTION_SESSION_KEY = 'Ccasje5XNKck2ThyavDgvGdKSvvYgFnU';

    /**
     * @Route("", name="home")
     */
    public function index()
    {
        return $this->render('home/index.html.twig');
    }

    /**
     * @Route("/releases", name="home_releases")
     */
    public function releases(SpotifyService $spotifyService)
    {
        $releases = json_decode($this->paginatedReleases($spotifyService)->getContent());

        if($releases === false){
            $this->get('session')->getFlashBag()->set('error', 'No se han podido consultar los nuevos lanzamientos, por favor intentalo mas tarde');
//            $this->addFlash(self::ERROR_CONNECTION_SESSION_KEY, 'No se han podido consultar los nuevos lanzamientos, por favor intentalo mas tarde');
            return $this->redirectToRoute('home');
        }

        return $this->render('home/releases.html.twig', [
            'releases' => $releases,
        ]);
    }

    /**
     * @Route("/paginated-releases/{offset}", name="home_paginated_releases", options={"expose"=true}, methods={"GET"})
     */
    public function paginatedReleases(SpotifyService $spotifyService = null, $offset = 0){
        return $this->json($spotifyService->getNewReleases($offset));
    }

    /**
     * @Route("/artist/{id}", name="home_artist", options={"expose"=true})
     */
    public function artist(SpotifyService $spotifyService, $id)
    {
        $artist = $spotifyService->getArtist($id);
        $topTracks = $spotifyService->getArtistsTopTracks($id);

        if($artist === false || $topTracks === false){
            $this->addFlash(self::ERROR_CONNECTION_SESSION_KEY, 'No se ha podido consultar la información del artista, por favor intentalo mas tarde');
            return $this->redirectToRoute('home');
        }

        $artist->topTracks = $topTracks;

        return $this->render('home/artist.html.twig', [
            'artist' => $artist
        ]);
    }
}
