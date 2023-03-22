<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Services\FavorisManager;

class FavorisController extends AbstractController
{   
        
    /**
     * @Route("/favorisList", name="app_favoris_list")
     */
    public function favorisList(FavorisManager $favorisManager): Response
    {
        $listeFavoris = $favorisManager->getAll();

        return $this->render('favoris/index.html.twig', [
            'listFavoris'=> $listeFavoris
        ]);
    }

    /**
     * @Route("/favoris/{idFavoris}", name="app_favoris", requirements={"idFavoris":"\d+"})
     */
    public function index($idFavoris, FavorisManager $favorisManager): Response
    {
        // j'utilise le service
        $favorisManager->addOrRemove($idFavoris);
        
        // todo : Faire la redirection vers la page favoris
        return $this->redirectToRoute('app_home');
    }

}
