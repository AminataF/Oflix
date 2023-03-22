<?php

namespace App\Security\Voter;

use App\Entity\Movie;
use DateTime;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

class Movie1400Voter extends Voter
{

    /**
     * Est ce que je sais gérer ce droit ?
     *
     * @param string $attribute le nom de la règle
     * @param mixed $subject l'objet lié
     * @return boolean
     */
    protected function supports(string $attribute, $subject): bool
    {
        // Si je suis le ROLE_VOTER de symfony, mon code serait
        // $attribute startwith "ROLE_"

        $isAttributOK = false;
        if ($attribute === "MOVIE1400"){
            $isAttributOK = true;
        }

        
        // Si je suis le ROLE_VOTER de symfony, mon code serait
        // $subject instanceof \App\Entity\User;
        
        // je m'assure que le sujet est une Entité de type Movie
        // $isSubjectOk = ($subject instanceof \App\Entity\Movie);

        return $isAttributOK;
    }

    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();
        // if the user is anonymous, do not grant access
        if (!$user instanceof UserInterface) {
            return false;
        }

        // le sujet est une Entité Movie
        // parce que on l'a testé : ($subject instanceof \App\Entity\Movie);
        if ($subject->getTitle() == "Dicta sed omnis soluta."){
            // exectpion faites pour ce film
            return true;
        }

        $now = new DateTime();
        // G	Heure, au format 24h, sans les zéros initiaux	0 à 23
        // i	Minutes avec les zéros initiaux	00 à 59
        $hour = $now->format("Gi"); // 1320 / 959 / 2359 / 001
        if ($hour > 1300){
            // il est plus de 14h, je lance une exception AccesDenied
            return false;
        } else {
            // il est moins de 14h00
            return true;
        }

        return false;
    }
}
