<?php

namespace App\Controller\Backoffice;

use App\Entity\Season;
use App\Form\SeasonType;
use App\Repository\SeasonRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * en ajoutant une route sur la classe, cela préfixe toutes les routes de la classe
 * 
 * @Route("/backoffice/season")
 */
class SeasonController extends AbstractController
{
    /**
     * préfixe + / => /backoffice/season/
     * 
     * @Route("/", name="app_backoffice_season_index", methods={"GET"})
     */
    public function index(SeasonRepository $seasonRepository): Response
    {
        return $this->render('backoffice/season/index.html.twig', [
            'seasons' => $seasonRepository->findAll(),
        ]);
    }

    /**
     * préfixe + / => /backoffice/season/new
     * 
     * @Route("/new", name="app_backoffice_season_new", methods={"GET", "POST"})
     */
    public function new(Request $request, SeasonRepository $seasonRepository): Response
    {
        $season = new Season();
        $form = $this->createForm(SeasonType::class, $season);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $seasonRepository->add($season, true);

            return $this->redirectToRoute('app_backoffice_season_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('backoffice/season/new.html.twig', [
            'season' => $season,
            'form' => $form,
        ]);
    }

    /**
     * @Route("/{id}", name="app_backoffice_season_show", methods={"GET"})
     */
    public function show(Season $season): Response
    {
        return $this->render('backoffice/season/show.html.twig', [
            'season' => $season,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="app_backoffice_season_edit", methods={"GET", "POST"})
     */
    public function edit(Request $request, Season $season, SeasonRepository $seasonRepository): Response
    {
        $form = $this->createForm(SeasonType::class, $season);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $seasonRepository->add($season, true);

            return $this->redirectToRoute('app_backoffice_season_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('backoffice/season/edit.html.twig', [
            'season' => $season,
            'form' => $form,
        ]);
    }

    /**
     * @Route("/{id}", name="app_backoffice_season_delete", methods={"POST"})
     */
    public function delete(Request $request, Season $season, SeasonRepository $seasonRepository): Response
    {
        $tokenFromFormulaire = $request->request->get('_token');
        //       {{ csrf_token('delete' ~ season.id) }}

        $seedTokenController = 'delete' . $season->getId();

        if ($this->isCsrfTokenValid($seedTokenController, $tokenFromFormulaire)) {
            $seasonRepository->remove($season, true);
        }

        return $this->redirectToRoute('app_backoffice_season_index', [], Response::HTTP_SEE_OTHER);
    }
}
