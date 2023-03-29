<?php

namespace App\Controller;

use App\Entity\Participant;
use App\Entity\Sortie;
use App\Form\ParticipantType;
use App\Repository\ParticipantRepository;
use App\Repository\SortieRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ParticipantController extends AbstractController
{
    #[Route('/profil/{pseudo}', name: 'participant_profil', requirements: ['pseudo'=>'^(?!modifier$).*$'])]
    public function detail(Participant $participant): Response
    {
        if (!$participant){
            throw $this->createNotFoundException('Ce pseudo n\'existe pas.');
        }
        return $this->render('participant/profil.html.twig',
            compact('participant')
            );
    }
    #[Route('/profil/modifier', name: 'participant_modifier' )]
    public function modifier(EntityManagerInterface $entityManager,
                             Request $request,


    ):Response{


        $participant = $this->getUser();
        $participantform = $this->createForm(ParticipantType::class, $participant);
        $participantform->handleRequest($request);
        if($participantform->isSubmitted()&&$participantform->isValid()){
            $entityManager->persist($participant);
            $entityManager->flush();
            return $this->redirectToRoute('participant_modifier');
        }

        return $this->render('participant/modifier.html.twig', compact(
            'participantform'
        ));
    }

    #[Route("/sortie/ajouter/{sortie_id}", name: "ajouter_sortie")]
    public function ajouterSortie(
        int $sortie_id,
        EntityManagerInterface $manager,
        participantRepository $participantRepository,
        SortieRepository $sortieRepository
    ): Response
    {

        $sortie=$sortieRepository->find($sortie_id);

        $participant=$this->getUser();

        //$participant = $participantRepository->findOneBy(["email" => $this->getUser()->getUserIdentifier()]);

        $participant->addSorty($sortie);
        $manager->persist($participant);
        $manager->flush();
        $this->addFlash("succes", $sortie->getNom() . " a été ajouté en ami par " . $participant->getPseudo());
        return $this->redirectToRoute("sortie_liste");
    }
}
