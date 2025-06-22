<?php

namespace App\Controller\Admin;

use App\Entity\User;
use App\Entity\Visite;
use App\Entity\Visiteur;
use App\Repository\VisiteRepository;
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
    private VisiteRepository $visiteRepository;

    public function __construct(AdminUrlGenerator $adminUrlGenerator, VisiteRepository $visiteRepository)
    {
        $this->adminUrlGenerator = $adminUrlGenerator;
        $this->visiteRepository = $visiteRepository;
        
    }

    public function index(): Response
    {
        // Récupérer les statistiques des visites par mois
        $visitesParMois = $this->getVisitesParMois();
        
        return $this->render('admin/dashboard.html.twig', [
            'visitesParMois' => $visitesParMois,
            'visitesParGuideParMois' => $this->getVisitesParGuideParMois(),

        ]);
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

   private function getVisitesParMois(): array
{
    $annee = date('Y');
    $dateDebut = new \DateTime($annee . '-01-01');
    $dateFin = new \DateTime($annee . '-12-31 23:59:59');
    
    // Récupérer toutes les visites de l'année avec une condition BETWEEN
    $visites = $this->visiteRepository->createQueryBuilder('v')
        ->select('v.date')
        ->where('v.date BETWEEN :dateDebut AND :dateFin')
        ->setParameter('dateDebut', $dateDebut)
        ->setParameter('dateFin', $dateFin)
        ->getQuery()
        ->getResult();

    // Grouper les visites par mois en PHP
    $visitesParMois = [];
    for ($i = 1; $i <= 12; $i++) {
        $visitesParMois[$i] = 0;
    }

    foreach ($visites as $visite) {
        $mois = (int) $visite['date']->format('n');
        $visitesParMois[$mois]++;
    }

    // Créer le tableau final avec les noms des mois
    $moisNoms = [
        1 => 'Janvier', 2 => 'Février', 3 => 'Mars', 4 => 'Avril',
        5 => 'Mai', 6 => 'Juin', 7 => 'Juillet', 8 => 'Août',
        9 => 'Septembre', 10 => 'Octobre', 11 => 'Novembre', 12 => 'Décembre'
    ];

    $donnees = [];
    foreach ($moisNoms as $numeroMois => $nomMois) {
        $donnees[] = [
            'mois' => $nomMois,
            'nombre' => $visitesParMois[$numeroMois]
        ];
    }

    return $donnees;
}

private function getVisitesParGuideParMois(): array
{
    $annee = date('Y');
    $dateDebut = new \DateTime($annee . '-01-01');
    $dateFin = new \DateTime($annee . '-12-31 23:59:59');

    $visites = $this->visiteRepository->createQueryBuilder('v')
        ->join('v.guide', 'g')
        ->select('g.prenom AS prenom', 'g.nom AS nom', 'v.date')
        ->where('v.date BETWEEN :dateDebut AND :dateFin')
        ->setParameter('dateDebut', $dateDebut)
        ->setParameter('dateFin', $dateFin)
        ->getQuery()
        ->getResult();

    $resultats = [];

    for ($mois = 1; $mois <= 12; $mois++) {
        $moisNom = \DateTime::createFromFormat('!m', $mois)->format('F');
        $resultats[$moisNom] = [];
    }

    foreach ($visites as $visite) {
        $moisNom = \DateTime::createFromFormat('!m', $visite['date']->format('n'))->format('F');
        $guideNom = $visite['prenom'] . ' ' . $visite['nom'];

        if (!isset($resultats[$moisNom][$guideNom])) {
            $resultats[$moisNom][$guideNom] = 0;
        }

        $resultats[$moisNom][$guideNom]++;
    }

    return $resultats;
}

private function getTauxPresenceParMois(): array
{
    $annee = date('Y');
    $dateDebut = new \DateTime("$annee-01-01");
    $dateFin = new \DateTime("$annee-12-31 23:59:59");

    $visiteurs = $this->getDoctrine()->getManager()
        ->getRepository(Visiteur::class)
        ->createQueryBuilder('v')
        ->join('v.visite', 'visite')
        ->where('visite.date BETWEEN :start AND :end')
        ->setParameter('start', $dateDebut)
        ->setParameter('end', $dateFin)
        ->getQuery()
        ->getResult();

    $tauxParMois = [];
    for ($i = 1; $i <= 12; $i++) {
        $tauxParMois[$i] = ['total' => 0, 'present' => 0];
    }

    foreach ($visiteurs as $visiteur) {
        $mois = (int) $visiteur->getVisite()->getDate()->format('n');
        $tauxParMois[$mois]['total']++;
        if ($visiteur->isPresent()) {
            $tauxParMois[$mois]['present']++;
        }
    }

    $resultat = [];
    $moisNoms = [
        1 => 'Janvier', 2 => 'Février', 3 => 'Mars', 4 => 'Avril',
        5 => 'Mai', 6 => 'Juin', 7 => 'Juillet', 8 => 'Août',
        9 => 'Septembre', 10 => 'Octobre', 11 => 'Novembre', 12 => 'Décembre'
    ];

    foreach ($tauxParMois as $mois => $data) {
        $pourcentage = $data['total'] > 0 ? round(($data['present'] / $data['total']) * 100, 1) : 0;
        $resultat[] = [
            'mois' => $moisNoms[$mois],
            'taux' => $pourcentage,
        ];
    }

    return $resultat;
}



}