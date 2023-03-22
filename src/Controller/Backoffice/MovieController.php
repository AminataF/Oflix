<?php

namespace App\Controller\Backoffice;

use App\Entity\Movie;
use App\Form\MovieType;
use App\Repository\MovieRepository;
use DateTime;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
// annotation @IsGranted
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\String\Slugger\SluggerInterface;

/**
 * @Route("/backoffice/movie")
 */
class MovieController extends AbstractController
{
    /**
     * @Route("/", name="app_backoffice_movie_index", methods={"GET"})
     */
    public function index(MovieRepository $movieRepository): Response
    {
        return $this->render('backoffice/movie/index.html.twig', [
            'movies' => $movieRepository->findAll(),
        ]);
    }

    /**
     * @Route("/new", name="app_backoffice_movie_new", methods={"GET", "POST"})
     * 
     * cette annotation va lancer une exception 403
     * @IsGranted("ROLE_ADMIN", message="No access! Get out!")
     * 
     */
    public function new(Request $request, MovieRepository $movieRepository, SluggerInterface $sluggerInterface): Response
    {
        // cela lance une exception, qui arrête notre code immédiatement
        // cette ligne est strictement identique au fonctionnement du security.yaml
        // $this->denyAccessUnlessGranted("ROLE_ADMIN");
        // à partir d'ici, mon utilisateur à le ROLE_ADMIN

        if (!$this->isGranted("ROLE_ADMIN"))
        {
            // ici mon utilisateur n'a pas le ROLE_ADMIN
            // je redirige mon utilisateur
            return $this->redirectToRoute("app_backoffice_movie_index");
        }

        $movie = new Movie();
        $form = $this->createForm(MovieType::class, $movie);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $movieRepository->add($movie, true);

            $slugTitle = $sluggerInterface->slug($movie->getTitle());
            $movie->setSlug($slugTitle);
            return $this->redirectToRoute('app_backoffice_movie_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('backoffice/movie/new.html.twig', [
            'movie' => $movie,
            'form' => $form,
        ]);
    }

    /**
     * @Route("/{id}", name="app_backoffice_movie_show", methods={"GET"})
     */
    public function show(Movie $movie): Response
    {
        return $this->render('backoffice/movie/show.html.twig', [
            'movie' => $movie,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="app_backoffice_movie_edit", methods={"GET", "POST"})
     */
    public function edit(Request $request, Movie $movie, MovieRepository $movieRepository, SluggerInterface $sluggerInterface): Response
    {
        // TODO : on a pas le droit de modifier un Movie après 14H00
        $isGranted = $this->isGranted("TAGADA", $movie);

        // $now = new DateTime();
        // // G	Heure, au format 24h, sans les zéros initiaux	0 à 23
        // // i	Minutes avec les zéros initiaux	00 à 59
        // $hour = $now->format("Gi"); // 1320 / 959 / 2359 / 001
        // if ($hour > 1300){
        //     // il est plus de 14h, je lance une exception AccesDenied
        //     throw $this->createAccessDeniedException("Il est au dela de 14h, vous ne pouvez pas modifier ce film");
        // }
        
        $form = $this->createForm(MovieType::class, $movie);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $movieRepository->add($movie, true);

            $slugTitle = $sluggerInterface->slug($movie->getTitle());
            $movie->setSlug($slugTitle);

            return $this->redirectToRoute('app_backoffice_movie_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('backoffice/movie/edit.html.twig', [
            'movie' => $movie,
            'form' => $form,
        ]);
    }

    /**
     * @Route("/{id}", name="app_backoffice_movie_delete", methods={"POST"})
     */
    public function delete(Request $request, Movie $movie, MovieRepository $movieRepository): Response
    {
        if ($this->isCsrfTokenValid('delete'.$movie->getId(), $request->request->get('_token'))) {
            $movieRepository->remove($movie, true);
        }

        return $this->redirectToRoute('app_backoffice_movie_index', [], Response::HTTP_SEE_OTHER);
    }
}
