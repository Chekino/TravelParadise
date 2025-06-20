<?php

namespace App\Controller\Admin;

use App\Entity\User;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;

use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;


class UserCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return User::class;
    }

    public function configureFields(string $pageName): iterable
    {
        // Détection du rôle de l'utilisateur connecté
        $connectedUser = $this->getUser();
        $roles = $connectedUser ? $connectedUser->getRoles() : [];

        $roleChoices = [];

        if (in_array('ROLE_ADMIN', $roles)) {
            $roleChoices = [
                'Administrateur' => 'ROLE_ADMIN',
                'Utilisateur'    => 'ROLE_USER',
                'Guide'          => 'ROLE_GUIDE',
            ];
        } elseif (in_array('ROLE_USER', $roles)) {
            $roleChoices = [
                'Guide' => 'ROLE_GUIDE',
            ];
        }

        return [
            IdField::new('id')->onlyOnIndex(),
            TextField::new('email'),
            TextField::new('nom'),
            TextField::new('prenom'),
            TextField::new('password')->hideOnIndex(),
            BooleanField::new('statut')->onlyOnIndex(),
            
            TextField::new('paysAffectation')->hideOnIndex(),
            TextField::new('photo')->hideOnIndex(),
            
            ChoiceField::new('mainRole')
                ->setLabel('Rôle')
                ->setChoices($roleChoices)
                ->allowMultipleChoices(false)
                ->renderExpanded(),
        ];
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Utilisateur')
            ->setEntityLabelInPlural('Utilisateurs')
            ->setPageTitle('index', 'Gestion des utilisateurs')
            ->setPageTitle('new', 'Créer un utilisateur')
            ->setPageTitle('edit', 'Modifier l\'utilisateur')
            ->setPageTitle('detail', 'Détails de l\'utilisateur')
            ->setFormOptions([
                'attr' => [
                    'data-controller' => 'role-dependent-fields'
                ]
            ]);
    }

    public function persistEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        /** @var User $user */
        $user = $entityInstance;
        
        // Convertir le rôle principal en array
        if ($user->getMainRole()) {
            $user->setRoles([$user->getMainRole()]);
        }

        parent::persistEntity($entityManager, $entityInstance);
    }

    public function updateEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        /** @var User $user */
        $user = $entityInstance;

        // Hasher le mot de passe seulement si un nouveau mot de passe a été fourni
        if ($user->getPassword()) {
            $hashedPassword = $this->passwordHasher->hashPassword($user, $user->getPassword());
            $user->setPassword($hashedPassword);
        }
        
        // Convertir le rôle principal en array
        if ($user->getMainRole()) {
            $user->setRoles([$user->getMainRole()]);
        }

        parent::updateEntity($entityManager, $entityInstance);
    }
}