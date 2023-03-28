<?php

namespace App\Controller;

use App\Entity\Participant;
use App\Form\ParticipantType;
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
            return $this->redirectToRoute('/profil/'.$participant->getPseudo());
        }

        return $this->render('participant/modifier.html.twig', compact(
            'participantform'
        ));
    }
}
