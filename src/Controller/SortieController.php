<?php

namespace App\Controller;


use App\Entity\Etat;
use App\Entity\Lieu;
use App\Entity\Sortie;
use App\Form\FormType;
use App\Form\SortieType;
use App\Repository\EtatRepository;
use App\Repository\LieuRepository;
use App\Repository\ParticipantRepository;
use App\Repository\SortieRepository;


use App\Repository\VilleRepository;
use DateInterval;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Constraints\NotBlank;

class SortieController extends AbstractController
{
    #[Route('/creation', name: 'sortie_creation')]
    public function cree(
        request                $request,
        EntityManagerInterface $entityManager,
        EtatRepository         $etatRepository,
        lieuRepository         $lieuRepository): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');
        $etat = $etatRepository->findOneBy(['id' => 1]);
        $sortie = new Sortie();
        $sortie->setEtat($etat);


        $sortie->setOrganisateur($this->getUser());
        $sortieForm = $this->createForm(SortieType::class, $sortie);


        $sortieForm->handleRequest($request);
        if ($sortieForm->isSubmitted() && $sortieForm->isValid()) {

             try {

                $lieu = $sortie->getLieu();

                $entityManager->persist($lieu);
                $entityManager->flush();

                $entityManager->persist($sortie);
                $entityManager->flush();

                $sortieForm = $this->createForm(SortieType::class, $sortie);


                return $this->redirectToRoute('sortie_liste');
           } catch (\Exception $exception) {
               $this->addFlash('echec', 'La sortie n\'a pas pu être ajoutée');

               return $this->redirectToRoute('sortie_creation');
            }
        }
        return $this->render('sortie/creation.html.twig', compact('sortieForm'));
    }
    #[Route('/lieu', name: 'get_lieux_by_ville')]
    public function getLieuxByVille(Request $request, VilleRepository $villeRepository)
    {
        $villeId = $request->get('ville');
        $lieux = $villeRepository->findBy(['ville' => $villeId]);

        $responseArray = array();
        foreach ($lieux as $lieu) {
            $responseArray[] = array(
                'id' => $lieu->getId(),
                'nom' => $lieu->getNom()
            );
        }
dump($responseArray);
        return new JsonResponse($responseArray);
    }



    #[Route('/supprimer/{suppression_id}', name: 'sortie_suppression', requirements: ['suppression_id' => '\d+'])]
    public function supprimer(Sortie $suppression_id, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');
        $entityManager->remove($suppression_id);
        $entityManager->flush();

        return $this->redirectToRoute('sortie_liste', compact('suppression_id'));
    }

    #[Route('/modification/{sortie}', name: 'sortie_modification', requirements: ['sortie' => '\d+'])]
    public function modification(
        Sortie                 $sortie,
        request                $request,
        EntityManagerInterface $entityManager
    ): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');
        $sortieForm = $this->createForm(SortieType::class, $sortie);

        $sortieForm->handleRequest($request);
        if ($sortieForm->isSubmitted()) {


            $entityManager->persist($sortie);
            $entityManager->flush();

            return $this->redirectToRoute('sortie_detail', ['detail_id' => $sortie->getId()]);
        }

        return $this->render('sortie/modification.html.twig',  compact('sortieForm'));
    }


    #[Route('/detail/{detail_id}', name: 'sortie_detail', requirements: ['detail_id' => '\d+'])]
    public function detail(Sortie $detail_id): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');
        return $this->render('sortie/detail.html.twig', compact('detail_id'));
    }

    #[Route('/liste', name: 'sortie_liste')]
    public function liste(
        SortieRepository       $sortieRepository,
        EtatRepository         $etatRepository,
        EntityManagerInterface $entityManager,
        request                $request

    ): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');
        $maintenant = new DateTime();

        $sorties = $sortieRepository->findAll();
        $nbreSortie = count($sorties);



        foreach ($sorties as $element) {

            $debut = $element->getDateHeureDebut();
            $limite = $element->getDateLimiteInscription();

            $dureeEnMinutes = $element->getDuree(); // Récupérer la valeur de la durée depuis l'objet $sortie

            $interval1 = new DateInterval('PT' . $dureeEnMinutes . 'M');
            $fin = $debut->add($interval1);

            $interval2 = new DateInterval('P1M');
            $archive = $debut->add($interval2);

            if ($maintenant < $debut) {

                $etat = $etatRepository->find(1);
                $element->setEtat($etat);
                $entityManager->persist($element);
                $entityManager->flush();
            }

            if ($maintenant < $limite) {

                $etat = $etatRepository->find(2);
                $element->setEtat($etat);
                $entityManager->persist($element);
                $entityManager->flush();
            }

            if ($maintenant > $debut && $maintenant < $fin) {

                $etat = $etatRepository->find(3);
                $element->setEtat($etat);
                $entityManager->persist($element);
                $entityManager->flush();
            }

            if ($maintenant > $limite) {

                $etat = $etatRepository->find(4);
                $element->setEtat($etat);
                $entityManager->persist($element);
                $entityManager->flush();
            }

            if ($maintenant > $fin && $maintenant < $archive) {
                $etat = $etatRepository->find(5);
                $element->setEtat($etat);
                $entityManager->persist($element);
                $entityManager->flush();
            }

            if ($maintenant > $archive) {
                $etat = $etatRepository->find(6);
                $element->setEtat($etat);
                $entityManager->persist($element);
                $entityManager->flush();
            }
        }

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

            return $this->render('sortie/resultats.html.twig', [
                'sortiessansdoublons' => $sortiessansdoublons,
                'sortiesavecdoublons'=>$sortiesavecdoublons
            ]);
        }

        //return $this->render('recherche/index.html.twig', ['form' => $form->createView()]);
        return $this->render('sortie/liste.html.twig', compact('sorties','nbreSortie','form')

        );

    }

    #[Route('/organisateur', name: 'sortie_organisateur')]
    public function organisateur(
        SortieRepository       $sortieRepository,
        EtatRepository         $etatRepository,
        EntityManagerInterface $entityManager,
        request                $request
    ): Response
    {


        $sorties = $sortieRepository->findBy(["organisateur" => $this->getUser()]);

        return $this->render('sortie/organisateur.html.twig', compact('sorties')

        );

    }



}

