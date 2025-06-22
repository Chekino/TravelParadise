<?php

namespace App\Controller;

use App\Entity\Visite;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;


#[IsGranted('ROLE_GUIDE')]
final class GuideController extends AbstractController
{
   #[Route('/guide', name: 'guide_dashboard')]
public function guide(EntityManagerInterface $em): Response
{
    $guide = $this->getUser();
    $now = new \DateTime();

    $visitesRepo = $em->getRepository(Visite::class);

    $toutesVisites = $visitesRepo->findBy(['guide' => $guide]);

    $visitesAVenir = [];
    $visitesEnCours = [];
    $visitesTerminees = [];

    foreach ($toutesVisites as $visite) {
        $date = $visite->getDate();
        $heureDebut = $visite->getHeureDebut();
        $heureFin = $visite->getHeureFin();

        $dateDebut = (new \DateTime())->setTimestamp($date->getTimestamp())
            ->setTime((int) $heureDebut->format('H'), (int) $heureDebut->format('i'));
        $dateFin = (new \DateTime())->setTimestamp($date->getTimestamp())
            ->setTime((int) $heureFin->format('H'), (int) $heureFin->format('i'));

        if ($dateDebut > $now) {
            $visitesAVenir[] = $visite;
        } elseif ($dateDebut <= $now && $dateFin > $now) {
            $visitesEnCours[] = $visite;
        } else {
            $visitesTerminees[] = $visite;
        }
    }

    return $this->render('guide/index.html.twig', [
        'visites_avenir' => $visitesAVenir,
        'visites_encours' => $visitesEnCours,
        'visites_terme' => $visitesTerminees,
    ]);
}
#[Route('/guide/visite/{id}', name: 'guide_visite_detail')]
public function showVisite(Visite $visite, Request $request, EntityManagerInterface $em): Response
{
    $visiteurs = $visite->getVisiteurs();

    // Mise à jour des présences/commentaires visiteurs
    if ($request->isMethod('POST')) {
        foreach ($visiteurs as $visiteur) {
            $presentKey = 'present_' . $visiteur->getId();
            $commentaireKey = 'commentaire_' . $visiteur->getId();

            $present = $request->request->get($presentKey);
            $commentaire = $request->request->get($commentaireKey);

            $visiteur->setPresent($present === 'oui');
            $visiteur->setCommentaire($commentaire);
        }

        // Commentaire final
        $commentaireFinal = $request->request->get('commentaire_final');
        $visite->setCommentaireFinal($commentaireFinal);

        $em->flush();

        $this->addFlash('success', 'Informations mises à jour avec succès.');
        return $this->redirectToRoute('guide_dashboard');
    }

    return $this->render('guide/show.html.twig', [
        'visite' => $visite,
        'visiteurs' => $visiteurs,
    ]);
}


}
