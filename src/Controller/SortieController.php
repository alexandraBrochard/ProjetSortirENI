<?php

namespace App\Controller;


use App\Entity\Etat;
use App\Entity\Lieu;
use App\Entity\Sortie;
use App\Entity\Ville;
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

                $sortie->addParticipant($this->getUser());

                $lieu = $sortie->getLieu();

                $entityManager->persist($lieu);
                $entityManager->flush();
                $sortie->setCampus($this->getUser()->getCampus());
                $entityManager->persist($sortie);
                $entityManager->flush();


                $this->addFlash('success', 'Sortie correctement enregistrée !');

                $sortieForm = $this->createForm(SortieType::class, $sortie);

                return $this->redirectToRoute('sortie_liste');
            } catch (\Exception $exception) {

                $this->addFlash('echec', 'La sortie n\'a pas pu être ajoutée');

                return $this->redirectToRoute('sortie_creation');
            }
        }
        return $this->render('sortie/creation.html.twig', compact('sortieForm'));
    }

    #[Route('/lieu/{ville}', name: 'sortie_lieuxByVille')]
    public function LieuxByVille(
        Ville $ville
    ): Response
    {

        $lieux = $ville->getLieu();

        $recup = array();
        foreach ($lieux as $lieu) {
            $recup[] = array(
                'id' => $lieu->getId(),
                'nom' => $lieu->getNom()
            );
        }

        return new JsonResponse($recup);
    }


    #[Route('/supprimer/{suppression_id}', name: 'sortie_suppression', requirements: ['suppression_id' => '\d+'])]
    public function supprimer(Sortie $suppression_id, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');
        $entityManager->remove($suppression_id);
        $entityManager->flush();

        return $this->redirectToRoute('sortie_liste', compact('suppression_id'));
    }

    #[Route('/annulation/{sortie}', name: 'sortie_annulation', requirements: ['sortie' => '\d+'])]
    public function annuler(
        Sortie                 $sortie,
        EntityManagerInterface $entityManager,
        EtatRepository         $etatRepository): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');
        $etat = $etatRepository->findOneBy(['id' => 7]);

        $sortie->setEtat($etat);

        $entityManager->persist($sortie);
        $entityManager->flush();

        $this->addFlash('success', 'désolée, cette sortie a du être annulée !');

        return $this->redirectToRoute('sortie_liste', compact('sortie'));
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

            return $this->redirectToRoute('sortie_detail', ['sortie' => $sortie->getId()]);
        }

        return $this->render('sortie/modification.html.twig', compact('sortieForm'));
    }


    #[Route('/detail/{sortie}', name: 'sortie_detail', requirements: ['sortie' => '\d+'])]
    public function detail(Sortie $sortie): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');
        return $this->render('sortie/detail.html.twig', compact('sortie'));
    }

    #[Route('/liste', name: 'sortie_liste')]
    public function liste(
        SortieRepository       $sortieRepository,
        EtatRepository         $etatRepository,
        EntityManagerInterface $entityManager,
        request                $request

    ): Response
    {

        $maintenant = new DateTime();

        $sorties = $sortieRepository->findAll();
        $nbreSortie = count($sorties);


        foreach ($sorties as $element) {
            $nom = $element->getNom();
            $debut = $element->getDateHeureDebut();
            $limite = $element->getDateLimiteInscription();
            $nbMax = $element->getNbInscriptionsMax();
            $inscrits = $element->getParticipants();
            $nbinscrits = count($inscrits);

            $dureeEnMinutes = $element->getDuree(); // Récupérer la valeur de la durée depuis l'objet $sortie

            $interval1 = new DateInterval('PT' . $dureeEnMinutes . 'M');
            $fin = $debut->add($interval1);

            $interval2 = new DateInterval('P1M');
            $archive = $debut->add($interval2);

            if ($element->getEtat()->getId() != 7) {

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


                if ($maintenant > $limite) {

                    $etat = $etatRepository->find(4);
                    $element->setEtat($etat);
                    $entityManager->persist($element);
                    $entityManager->flush();
                }

                if ($nbinscrits >= $nbMax) {

                    $etat = $etatRepository->find(4);
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

            if ($campus != null) {
                $sortiesParCampus = $sortieRepository->findBy(['campus' => $campus]);
            }

            $sorties = array_merge($sorties1, $sorties2);
            $sorties = array_merge($sorties, $sorties3);
            $sorties = array_merge($sorties, $sorties4);
            $sorties = array_merge($sorties, $sorties5);
            $sorties = array_merge($sorties, $sorties6);
            $sorties = array_merge($sorties, $sortiesParCampus);
            $sortiessansdoublons = array_unique($sorties, SORT_REGULAR);
            $sortiesavecdoublons = $sorties;

            return $this->render('sortie/resultats.html.twig', [
                'sortiessansdoublons' => $sortiessansdoublons,
                'sortiesavecdoublons' => $sortiesavecdoublons
            ]);
        }
        //return $this->render('recherche/index.html.twig', ['form' => $form->createView()]);
        return $this->render('sortie/liste.html.twig', compact('sorties', 'nbreSortie', 'form')
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

    #[Route('/archives', name: 'sortie_archives')]
    public function archives(
        SortieRepository       $sortieRepository,
        EtatRepository         $etatRepository,
        EntityManagerInterface $entityManager,
        request                $request
    ): Response
    {

        $etat = $etatRepository->findOneBy(['id' => 6]);
        $sortie = new Sortie();
        $sortie->setEtat($etat);
        $sorties = $sortieRepository->findBy(["etat" => $etat]);

        return $this->render('sortie/archives.html.twig', compact('sorties')

        );

    }


}

