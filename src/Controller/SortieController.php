<?php

namespace App\Controller;

use App\Entity\Idee;
use App\Entity\Sortie;
use App\Form\SortieType;
use App\Repository\SortieRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SortieController extends AbstractController
{
    #[Route('/creation', name: 'sortie_creation')]
    public function cree(
        request $request,
        EntityManagerInterface $entityManager)
    :Response
    {

        $sortie = new Sortie();

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
        Sortie $sortie,
        request $request,
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





    #[Route('/publication', name: 'sortie_publication')]
    public function publication(): Response
    {
        return $this->render('sortie/publication.html.twig', [
            'publication' => 'publication',
        ]);
    }

    #[Route('/liste', name: 'sortie_liste')]
    public function liste(SortieRepository $sortieRepository): Response
    {
        $sorties=$sortieRepository->findAll();
        return $this->render('sortie/liste.html.twig', compact('sorties')


        );
    }

    #[Route('/detail/{detail_id}', name: 'sortie_detail',requirements: ['detail_id' => '\d+'])]
    public function detail(Sortie $detail_id): Response
    {

        return $this->render('sortie/detail.html.twig', compact('detail_id'));
    }
}

