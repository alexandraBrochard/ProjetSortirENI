<?php

namespace App\Controller;

use App\Entity\Ville;
use App\Form\VillesCollectionType;
use App\Form\VilleType;
use App\Repository\VilleRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
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
        $villes = $villeRepository->findALl();
        $ville = new Ville();
//        $ville->setNom('test');
//        $ville->setCodePostal('00000');

        $form = $this->createForm(VillesCollectionType::class, ['villes'=>$villes]);
        $form->handleRequest();


        return $this->render('admin/villes.html.twig', [
            'form' => $form
        ]);
    }

    #[Route('/admin/campus', name: 'admin_campus')]
    public function campus(): Response
    {

        return $this->render('admin/campus.html.twig', [

        ]);
    }

    #[Route('/admin/utilisateurs', name: 'admin_utilisateurs')]
    public function utilisateurs(): Response
    {

        return $this->render('admin/utilisateurs.html.twig', [

        ]);
    }
}
