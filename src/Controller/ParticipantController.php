<?php

namespace App\Controller;

use App\Entity\Participant;
use App\Entity\Sortie;
use App\Form\ParticipantType;
use App\Repository\ParticipantRepository;
use App\Repository\SortieRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

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
                             Request $request, SluggerInterface $slugger


    ):Response{

        $this->denyAccessUnlessGranted('ROLE_USER');
        $participant = $this->getUser();
        $participantform = $this->createForm(ParticipantType::class, $participant);
        $participantform->handleRequest($request);
        if($participantform->isSubmitted()&&$participantform->isValid()){
            /** @var UploadedFile $brochureFile */
            $brochureFile = $participantform->get('brochure')->getdata();
            // this condition is needed because the 'brochure' field is not required
            // so the PDF file must be processed only when a file is uploaded
            if ($brochureFile) {
                $originalFilename = pathinfo($brochureFile->getClientOriginalName(), PATHINFO_FILENAME);
                // this is needed to safely include the file name as part of the URL
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$brochureFile->guessExtension();

                // Move the file to the directory where brochures are stored
                try {
                    $brochureFile->move(
                        $this->getParameter('brochures_directory'),
                        $newFilename
                    );
                } catch (FileException $e) {
                    // ... handle exception if something happens during file upload
                }
                // updates the 'brochureFilename' property to store the PDF file name
                // instead of its contents
                $participant->setBrochureFilename($newFilename);
            }

            $entityManager->persist($participant);
            $entityManager->flush();
            $route = new Route('/profil/'.$participant->getPseudo());
            //$routeCollection->add('routeProfil', $route);
            return $this->redirectToRoute('participant_profil', ['pseudo'=>$participant->getPseudo()]);
        }

        return $this->render('participant/modifier.html.twig', compact(
            'participantform'
        ));

    }

    #[Route("/sortie/inscrire/{sortie_id}", name: "inscrire_sortie")]
    public function inscrireSortie(
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

    #[Route("/sortie/desinscrire/{sortie_id}", name: "desinscrire_sortie")]
    public function desinscrireSortie(
        int $sortie_id,
        EntityManagerInterface $manager,
        participantRepository $participantRepository,
        SortieRepository $sortieRepository
    ): Response
    {

        $sortie=$sortieRepository->find($sortie_id);

        $participant=$this->getUser();

        //$participant = $participantRepository->findOneBy(["email" => $this->getUser()->getUserIdentifier()]);

        $participant->removeSorty($sortie);
        $manager->persist($participant);
        $manager->flush();

        return $this->redirectToRoute("sortie_liste");
    }


}
