<?php

namespace App\Controller\Admin;

use App\Entity\User;
use App\Entity\Visite;
use App\Entity\Visiteur;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminDashboard;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\HttpFoundation\Response;

#[AdminDashboard(routePath: '/admin', routeName: 'admin')]
class AdminDashboardController extends AbstractDashboardController
{
    private AdminUrlGenerator $adminUrlGenerator;

    public function __construct(AdminUrlGenerator $adminUrlGenerator)
    {
        $this->adminUrlGenerator = $adminUrlGenerator;
    }

    public function index(): Response
    {
        // Redirection vers le CRUD Visite pour déclencher l'affichage du layout EasyAdmin
        $url = $this->adminUrlGenerator
            ->setController(\App\Controller\Admin\VisiteCrudController::class)
            ->generateUrl();

        return $this->redirect($url);
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('TravelParadise');
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::linkToDashboard('Tableau de bord', 'fa fa-home');

        if ($this->isGranted('ROLE_ADMIN')) {
            yield MenuItem::section('Gestion des comptes');
            yield MenuItem::linkToCrud('Utilisateurs', 'fas fa-users', User::class);
        }

        if ($this->isGranted('ROLE_ADMIN') || $this->isGranted('ROLE_USER')) {
            yield MenuItem::section('Gestion des visites');
            yield MenuItem::linkToCrud('Visites', 'fas fa-map-marked-alt', Visite::class);
            yield MenuItem::linkToCrud('Visiteurs', 'fas fa-user-friends', Visiteur::class);
        }

        yield MenuItem::linkToLogout('Déconnexion', 'fas fa-sign-out-alt');
    }
}