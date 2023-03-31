<?php

namespace App\Controller;

use App\Entity\Campus;
use App\Entity\Ville;
use App\Form\CampusType;
use App\Form\VillesCollectionType;
use App\Form\VilleType;
use App\Repository\CampusRepository;
use App\Repository\VilleRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;


#[IsGranted('ROLE_ADMIN')]
class AdminController extends AbstractController
{
    #[Route('/admin', name: 'app_admin')]
    public function index(): Response
    {

        return $this->render('admin/administration.html.twig', [

        ]);
    }
    #[Route('/admin/villes', name: 'admin_villes')]
    public function villes(EntityManagerInterface $entityManager,
                        Request $request, VilleRepository $villeRepository): Response
    {
        $ville = new Ville();
        $villes = $villeRepository->findALl();
        $villeForm = $this->createForm(VilleType::class, $ville);
        $villeForm->handleRequest($request);

        if ($villeForm->isSubmitted() && $villeForm->isValid()) {
            $entityManager->persist($ville);
            $entityManager->flush();
            return $this->redirectToRoute('admin_villes');
        }




        return $this->render('admin/villes.html.twig', [
            'villes' => $villes,
            'form' => $villeForm
        ]);
    }


    #[Route('/admin/villes/supprimer/{id}', name: 'admin_villes_suppr')]
    public function villeSupprimer(EntityManagerInterface $entityManager,
                           Request $request, VilleRepository $villeRepository, Ville $id): Response
    {
        $entityManager->remove($id);
        $entityManager->flush();

        return $this->redirectToRoute('admin_villes');
    }


    #[Route('/admin/campus', name: 'admin_campus')]
    public function campus(EntityManagerInterface $entityManager, CampusRepository $campusRepository, Request $request): Response
    {
        $campus = $campusRepository->findAll();
        $nouveauCampus = new Campus();
        $campusForm = $this->createForm(CampusType::class, $nouveauCampus);
        $campusForm->handleRequest($request);

        if ($campusForm->isSubmitted() && $campusForm->isValid()) {
            $entityManager->persist($nouveauCampus);
            $entityManager->flush();
            return $this->redirectToRoute('admin_campus');
        }


        return $this->render('admin/campus.html.twig', [
            'campus' => $campus,
            'form'=> $campusForm,
        ]);
    }

    #[Route('/admin/campus/supprimer/{id}', name: 'admin_campus_suppr')]
    public function campusSupprimer(EntityManagerInterface $entityManager,
                                   Request $request, CampusRepository $campusRepository, Campus $id): Response
    {
        $entityManager->remove($id);
        $entityManager->flush();

        return $this->redirectToRoute('admin_campus');
    }

    #[Route('/admin/utilisateurs', name: 'admin_utilisateurs')]
    public function utilisateurs(): Response
    {

        return $this->render('admin/utilisateurs.html.twig', [

        ]);
    }
}
