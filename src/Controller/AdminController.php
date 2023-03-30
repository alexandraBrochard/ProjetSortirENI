<?php

namespace App\Controller;

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
    #[Route('/admin/villes', name: 'admiin_villes')]
    public function villes(): Response
    {

        return $this->render('admin/villes.html.twig', [

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
