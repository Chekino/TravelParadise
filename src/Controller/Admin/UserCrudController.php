<?php

namespace App\Controller\Admin;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserCrudController extends AbstractCrudController
{
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    public static function getEntityFqcn(): string
    {
        return User::class;
    }

    public function configureFields(string $pageName): iterable
    {
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

        $fields = [
            IdField::new('id')->onlyOnIndex(),
            TextField::new('email'),
            TextField::new('nom'),
            TextField::new('prenom'),
            BooleanField::new('statut')->onlyOnIndex(),
            TextField::new('paysAffectation')->hideOnIndex(),
            TextField::new('photo')->hideOnIndex(),
            ChoiceField::new('mainRole')
                ->setLabel('Rôle')
                ->setChoices($roleChoices)
                ->allowMultipleChoices(false)
                ->renderExpanded(),
        ];

        if (in_array($pageName, [Crud::PAGE_NEW, Crud::PAGE_EDIT])) {
            $fields[] = TextField::new('password')
                ->setLabel('Mot de passe')
                ->setFormTypeOption('mapped', true)
                ->setRequired($pageName === Crud::PAGE_NEW);
        }

        return $fields;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Utilisateur')
            ->setEntityLabelInPlural('Utilisateurs')
            ->setPageTitle('index', 'Gestion des utilisateurs')
            ->setPageTitle('new', 'Créer un utilisateur')
            ->setPageTitle('edit', 'Modifier l\'utilisateur')
            ->setPageTitle('detail', 'Détails de l\'utilisateur');
    }

    public function persistEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        /** @var User $user */
        $user = $entityInstance;

        if ($user->getPassword()) {
            $hashedPassword = $this->passwordHasher->hashPassword($user, $user->getPassword());
            $user->setPassword($hashedPassword);
        }

        if ($user->getMainRole()) {
            $user->setRoles([$user->getMainRole()]);
        }

        parent::persistEntity($entityManager, $user);
    }

    public function updateEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        /** @var User $user */
        $user = $entityInstance;

        if ($user->getPassword()) {
            $hashedPassword = $this->passwordHasher->hashPassword($user, $user->getPassword());
            $user->setPassword($hashedPassword);
        }

        if ($user->getMainRole()) {
            $user->setRoles([$user->getMainRole()]);
        }

        parent::updateEntity($entityManager, $user);
    }
}
