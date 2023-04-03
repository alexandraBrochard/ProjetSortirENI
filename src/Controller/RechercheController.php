<?php

namespace App\Controller;

use App\Entity\Sortie;
use App\Form\FormType;
use App\Repository\ParticipantRepository;
use App\Repository\SortieRepository;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SearchType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class RechercheController extends AbstractController
{

    #[Route('/rechercher', name: 'recherche_search')]
    public function search(
        Request               $request,
        SortieRepository      $sortieRepository,
        ParticipantRepository $participantRepository): Response
    {
        $form = $this->createForm(FormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            $organisateur = $data['organisateur'];
            $sortiesDejaInscrit = $data['inscrit'];
            $sortiesNonInscrit = $data['noninscrit'];
            $textRecherche = $data['nom'];
            $debut1 = $data['debut1'];
            $debut2 = $data['debut2'];
            $passe = $data['passe'];
            $campus = $data['Campus'];

            $sorties = null;
            $sorties1 = [];
            $sorties2 = [];
            $sorties3 = [];
            $sorties4 = [];
            $sorties5 = [];
            $sorties6 = [];
            $sortiesParCampus = [];

            if ($organisateur == true) {
                $sorties1 = $sortieRepository->findby(["organisateur" => $this->getUser()]);
            }

            if ($sortiesDejaInscrit == true) {
                $sorties2 = $sortieRepository->findSorties($this->getUser());
            }


            if ($sortiesNonInscrit == true) {
                $sorties3 = $sortieRepository->findSortiesnoninscrite($this->getUser());
            }

            if ($textRecherche != null) {
                $sorties4 = $sortieRepository->findbySortiestext($textRecherche);

            }

            if (($debut1 != null) and ($debut2 != null)) {
                $sorties5 = $sortieRepository->findbySortiesdate($debut1, $debut2);

            }

            if ($passe != null) {
                $sorties6 = $sortieRepository->findSortiespasses();

            }
            if($campus != null) {
                $sortiesParCampus = $sortieRepository->findBy(['campus'=> $campus]);
            }


            $sorties= array_merge($sorties1,$sorties2);
            $sorties= array_merge($sorties,$sorties3);
            $sorties= array_merge($sorties,$sorties4);
            $sorties= array_merge($sorties,$sorties5);
            $sorties= array_merge($sorties,$sorties6);
            $sorties= array_merge($sorties,$sortiesParCampus);
            $sortiessansdoublons=array_unique($sorties,SORT_REGULAR);
            $sortiesavecdoublons=$sorties;

            return $this->render('recherche/resultats.html.twig', [
                'sortiessansdoublons' => $sortiessansdoublons,
                'sortiesavecdoublons'=>$sortiesavecdoublons
            ]);
        }

        return $this->render('recherche/index.html.twig', ['form' => $form->createView()]);
    }



}
