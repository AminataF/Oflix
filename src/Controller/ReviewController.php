<?php

namespace App\Controller;

use App\Entity\Movie;
use App\Entity\Review;
use App\Form\ReviewType;
use App\Repository\MovieRepository;
use App\Repository\ReviewRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ReviewController extends AbstractController
{
    /**
     * @Route("/review/{id}", name="app_review", requirements={"idReview" = "\d+"})
     */
    public function index(?Movie $movieFromRoute, Request $request, ReviewRepository $reviewRepository): Response
    {
        // * pour utiliser le ParamConverter (c'est une astuce si je ne suis pas a l'aise je peux revenir au repository et avoir accées a la method find et les autres)
        // 1. je donne un nom de propriété dans ma route : id
        // 2. je donne le type de l'entité dans mes arguments : Movie
        // Symfony va faire : 
        // Movie ? => Entité => Doctrine => MovieRepository
        // Doctrine va demander : Symfony tu a quoi comme info ?
        // j'ai une propriété id
        // OK : 
        // $movieFromRoute = movieRepository->find($id);

        // ! je dois gérer la 404
        // * j'ajoute le marqueur de nullité sur mon entité en argument : '?' : ?Movie
        // cela permet au paramConverter de me fournir la valeur 'null' si il ne trouve pas d'entité
        // il ne me reste plus qu'a tester cette valeur null, et lancer une exception
        if ($movieFromRoute === null){throw $this->createNotFoundException("Ce film n'existe pas.");}

        // dd($movieFromRoute);

        // TODO : ajouter un review sur un movie
        // * Mes besoins
        // 1. du film, via la route, donc parkmètre/paramètre de route avec {id}
        // 2. j'affiche le formulaire
        // 3. gestion du formulaire remplit : POST, donc restreindre les méthodes
        // 4. Add review, ReviewRepository
        // 5. une redirection vers la route app_show

        // $movie = $movieRepository->find($idReview);

        $newReview = new Review();
        $form = $this->createForm(ReviewType::class, $newReview);

        // on récupère les infos du formulaire, et on met à jour notre entité $newReview
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid())
        {
            // TODO : persist + flush
            // il nous manque l'association avec le film
            $newReview->setMovie($movieFromRoute);

            $reviewRepository->add($newReview, true);

            // redirection idMovie fais référence à l'id passer en parametre sur ma page show et twig show
            return $this->redirectToRoute('app_show', ["slug" => $movieFromRoute->getSlug()]);
        }
        
        return $this->renderForm('review/index.html.twig', [
            'formReview' => $form,
            'movie' => $movieFromRoute
        ]);
    }
}
