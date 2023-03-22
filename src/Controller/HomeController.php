<?php

namespace App\Controller;

use App\Entity\Movie;
use App\Repository\CastingRepository;
use App\Repository\GenreRepository;
use App\Repository\MovieRepository;
use App\Repository\ReviewRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{

    // TODO : router home : doit afficher la page d'accueil
    /**
     * @Route("/", name="app_home")
     */
    public function index(MovieRepository $movieRepository, GenreRepository $genreRepository): Response
    {
        // TODO : j'affiche la liste des genres
        // J'ai besoin de mon GenreRepository
        $allGenre = $genreRepository->findAll();
        // si je veux un tri sur mes genre j'utilise le findBy
        $allGenre = $genreRepository->findBy(
            // je n'ai pas de critères, mais je dois fournir un tableau, celui ci sera vide
            [],
            // je veux trier par 'name' et 'ASC' (ordre alphabétique)
            // dans le tableau je fournis: 
            // clé : la propriété 
            // value : DESC ou ASC
            ["name" => "ASC"],
            // les autres paramètres ont des valeur par défaut
            // je ne suis pas obligé de les fournir
        );
        // TODO :  je récupére les infos de mon Movierepository ou mes stockés mes requêtes sql
        $allMovies = $movieRepository->findAll();
        // render() appartien à AbstractController et permet l'affichage de twig
        // le premier paramètre c'est le chemin de la vue ( les templates sont dans des dossiers nommés comme le controller qui l'utilise)
        // le deuxiéme paramètre est un tableau avec les donées que l'on veux afficher sur notre pas twig

        
        return $this->render('home/index.html.twig', [
            'allMovies' => $allMovies,
            'allGenre' => $allGenre
        ]);
    }

    /**
     * Undocumented function
     *
     * @route("/show/{slug}", name="app_show", requirements={"slug"="^[a-zA-Z0-9]+(?:-[a-zA-Z0-9]+)*$"}))
     * 
     * @return Response
     */
    // TODO : route show : doit afficher le detail d'un film
    public function show($slug, MovieRepository $movieRepository, CastingRepository $castingRepository, ReviewRepository $reviewRepository): Response
    {
        // TODO : récupérer l'élément du tableau $show grâce à l'id
        $movie = $movieRepository->findOneBy(['slug' => $slug]);

        // TODO : si le film n'existe pas je doit renvoyer une 404
        // ! sinon cela va me faire une erreur coté twig
        if ($movie === null){
            // renvoyer une 404
            // on lance un exception 404 (notFound)
            // symfony va l'attraper et changer la réponse en réponse 404
            throw $this->createNotFoundException("le film n'existe pas");
        }
        // TODO : aller chercher les castings
        // et ne pas se servir de la relation dans twig
        // Casting : CastingRepository
        $allCastingFromMovie = $castingRepository->findBy(
            // critère : WHERE
            // ! on parle objet
            // on donne le nom de la propriété
            // et la valeur doit être dans ce cas un objet Movie
            [
                "movie" => $movie
            ],
            // ORDER BY
            // ! on parle objet
            [
                "creditOrder" => "ASC"
            ]
        );

        $allReviews = $reviewRepository->findBy(["movie" => $movie]);

        // ? ici les seasons ne sont pas chargées, 
        // ? il faut faire une boucle (s'en servir) 
        // ? pour Doctrine réagisse et aille les chercher en automatique
        return $this->render('home/show.html.twig', [
            "movieForView" => $movie,
            'allCastings' => $allCastingFromMovie,
            'reviews' => $allReviews
            
        ]);
    }
    
    /**
     * Undocumented function
     *
     * @route("/search", name="app_search")
     * 
     * @return Response
     */
    // TODO : route search/list : doit afficher un résultat de recherche
    public function search(MovieRepository $movieRepository): Response
    {
        // TODO :  je récupére les infos du tableau
        $allMoviesForSearch = $movieRepository->findAll();
        // render() appartien à AbstractController et permet l'affichage de twig
        // le premier paramètre c'est le chemin de la vue ( les templates sont dans des dossiers nommés comme le controller qui l'utilise)
        // le deuxiéme paramètre est un tableau avec les donées que l'on veux afficher sur notre pas twig
        return $this->render('home/search.html.twig', [
            'allMoviesForSearch' => $allMoviesForSearch,
        ]);
    }
}
