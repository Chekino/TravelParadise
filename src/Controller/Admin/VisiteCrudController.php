<?php

namespace App\Controller\Admin;

use App\Entity\Visite;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;


class VisiteCrudController extends AbstractCrudController
{
    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public static function getEntityFqcn(): string
    {
        return Visite::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            AssociationField::new('guide')
                ->setLabel('Guide')
                ->setFormTypeOption('choices', $this->getGuides()),
            TextField::new('titre'),
            TextField::new('photo')->hideOnIndex(),
            TextField::new('pays'),
            TextField::new('lieu'),
            DateField::new('date'),
            TimeField::new('heureDebut'),
            TimeField::new('heureFin')->onlyOnIndex()->setLabel('Heure de fin'),
            NumberField::new('duree')->setLabel('Durée en heure'),
            TextareaField::new('commentaire')->hideOnIndex(),
            
        ];

    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Visite')
            ->setEntityLabelInPlural('Visites')
            ->setPageTitle('index', 'Gestion des visites')
            ->setPageTitle('new', 'Créer une visite')
            ->setPageTitle('edit', 'Modifier la visite')
            ->setPageTitle('detail', 'Détails de la visite')
            ->setFormOptions([
                'attr' => [
                    'data-controller' => 'role-dependent-fields'
                ]
            ]);
    }

       public function configureActions(Actions $actions): Actions
{
    return $actions
        ->update(Crud::PAGE_INDEX, Action::NEW, function (Action $action) {
            return $action->setLabel('Ajouter visite');
        });
        
}

    private function getGuides(): array
    {
        $userRepository = $this->em->getRepository(User::class);
        $allUsers = $userRepository->findAll();
        $guides = [];

        foreach ($allUsers as $user) {
            if (in_array('ROLE_GUIDE', $user->getRoles())) {
                $guides[] = $user;
            }
        }

        return $guides;
    }
}
