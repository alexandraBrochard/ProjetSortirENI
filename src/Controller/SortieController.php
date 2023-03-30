<?php

namespace App\Controller;


use App\Entity\Etat;
use App\Entity\Sortie;
use App\Form\SortieType;
use App\Repository\EtatRepository;
use App\Repository\SortieRepository;


use DateInterval;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SortieController extends AbstractController
{
    #[Route('/creation', name: 'sortie_creation')]
    public function cree(
        request                $request,
        EntityManagerInterface $entityManager,
        EtatRepository         $etatRepository): Response
    {
        $etat = $etatRepository->findOneBy(['id' => 1]);
        $sortie = new Sortie();
        $sortie->setEtat($etat);


        $sortie->setOrganisateur($this->getUser());
        $sortieForm = $this->createForm(SortieType::class, $sortie);

        $sortieForm->handleRequest($request);
        if ($sortieForm->isSubmitted() && $sortieForm->isValid()) {
            try {

                $entityManager->persist($sortie);
                $entityManager->flush();
                $sortie = new Sortie();
                $sortieForm = $this->createForm(SortieType::class, $sortie);

                return $this->redirectToRoute('sortie_liste');
            } catch (\Exception $exception) {
                $this->addFlash('echec', 'La sortie n\'a pas pu être ajoutée');

                return $this->redirectToRoute('sortie_creation');
            }
        }
        return $this->render('sortie/creation.html.twig', compact('sortieForm'));
    }

    #[Route('/supprimer/{suppression_id}', name: 'sortie_suppression', requirements: ['suppression_id' => '\d+'])]
    public function supprimer(Sortie $suppression_id, EntityManagerInterface $entityManager): Response
    {
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

        $sortieForm = $this->createForm(SortieType::class, $sortie);

        $sortieForm->handleRequest($request);
        if ($sortieForm->isSubmitted()) {


            $entityManager->persist($sortie);
            $entityManager->flush();

            return $this->redirectToRoute('sortie_detail', ['detail_id' => $sortie->getId()]);
        }

        return $this->render('sortie/modification.html.twig', compact('sortieForm'));
    }


    #[Route('/detail/{detail_id}', name: 'sortie_detail', requirements: ['detail_id' => '\d+'])]
    public function detail(Sortie $detail_id): Response
    {

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
        return $this->render('sortie/liste.html.twig', compact('sorties','nbreSortie')

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

