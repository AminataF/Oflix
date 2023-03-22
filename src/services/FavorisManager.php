<?php

namespace App\Services;

use Symfony\Component\HttpFoundation\RequestStack;

class FavorisManager{

    private $requestStack;

    // Pour être sûr d'avoir accès à la session
    // on la met dans le constructeur. Via l'injection de dépendances 
    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
    }

    public function addOrRemove($movieId)
    {
        $session = $this->requestStack->getSession();     
        
        // on teste si la clé favoris existe, sinon on la créé avec un tableau vide
        if (!($session->has('favoris'))) {$session->set('favoris', []);} 
        
        // on récupère la liste des favoris
        $favList = $session->get('favoris');

        // on check si l'index est dans le tableau de favoris
        if (!(in_array($movieId, $favList))) { 
            // * l'index n'est pas dans le tableau
            // on ajoute l'index dans le tableau
            $favList[] = $movieId; 
            // on enregistre en session
            $session->set('favoris', $favList);
        } else {
            // * l'index est dans le tableau
            // TODO enlever le favoris
            // on va parcourir le tableau pour trouver l'index
            foreach ($favList as $key => $fav) {
                
                if ($movieId == $fav){
                    // on a trouvé l'index, on le supprime
                    unset($favList[$key]);
                }
            }
            // ! et on oublie pas d'enregistrer en session
            $session->set('favoris', $favList);
        }

    }

    public function getAll()
    {
        $session = $this->requestStack->getSession(); 
        // on récupère la liste des favoris
        $favList = $session->get('favoris');

        return $favList;
    }
}