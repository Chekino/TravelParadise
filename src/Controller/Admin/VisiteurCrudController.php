<?php

namespace App\Controller\Admin;

use App\Entity\Visiteur;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;


class VisiteurCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Visiteur::class;
    }

    
    public function configureFields(string $pageName): iterable
    {
         return [
        TextField::new('nom'),
        TextField::new('prenom'),
        BooleanField::new('present')->setLabel('Présence (Oui/Non)'),
        TextareaField::new('commentaire')->hideOnIndex(),
        AssociationField::new('visite')
            ->setLabel('Visite associée')
            ->setRequired(true),
        ];
    }
    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Visiteur')
            ->setEntityLabelInPlural('Visisteurs')
            ->setPageTitle('index', 'Gestion des visiteurs')
            ->setPageTitle('new', 'Créer un visiteur')
            ->setPageTitle('edit', 'Modifier visiteur')
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
            return $action->setLabel('Ajouter visiteur');
        });
        
}
    
}
