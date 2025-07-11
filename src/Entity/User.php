<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_EMAIL', fields: ['email'])]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180)]
    private ?string $email = null;

    /**
     * @var list<string> The user roles
     */
    #[ORM\Column]
    private array $roles = [];

    /**
     * @var string The hashed password
     */
    #[ORM\Column]
    private ?string $password = null;

    #[ORM\Column(length: 255)]
    private ?string $nom = null;

    #[ORM\Column(length: 255)]
    private ?string $prenom = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $photo = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $paysAffectation = null;

    #[ORM\Column(nullable: true)]
    private ?bool $statut = null;

    /**
     * @var Collection<int, Visite>
     */
    #[ORM\OneToMany(targetEntity: Visite::class, mappedBy: 'guide')]
    private Collection $visites;

    public function __construct()
    {
        $this->visites = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;

        if (!in_array('ROLE_ADMIN', $roles) && !in_array('ROLE_GUIDE', $roles)) {
        $roles[] = 'ROLE_USER';
           }

        return array_unique($roles);
    }

    /**
     * @param list<string> $roles
     */
    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }


/**
 * Méthode virtuelle pour EasyAdmin - récupère le premier rôle
 */
public function getMainRole(): ?string
{
    $roles = $this->getRoles();
    return isset($roles[0]) ? $roles[0] : 'ROLE_USER';
}

/**
 * Méthode virtuelle pour EasyAdmin - définit un seul rôle
 */
public function setMainRole(?string $role): self
{
    $this->roles = $role ? [$role] : ['ROLE_USER'];

    // Automatiquement définir le statut à "actif" si c'est un guide
    if ($role === 'ROLE_GUIDE' && $this->statut === null) {
        $this->statut = 'actif';
    }
    // Effacer le statut si ce n'est plus un guide
    elseif ($role !== 'ROLE_GUIDE') {
        $this->statut = null;
    }
    return $this;
}

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): static
    {
        $this->nom = $nom;

        return $this;
    }

    public function getPrenom(): ?string
    {
        return $this->prenom;
    }

    public function setPrenom(string $prenom): static
    {
        $this->prenom = $prenom;

        return $this;
    }

    public function getPhoto(): ?string
    {
        return $this->photo;
    }

    public function setPhoto(?string $photo): static
    {
        $this->photo = $photo;

        return $this;
    }

    public function getPaysAffectation(): ?string
    {
        return $this->paysAffectation;
    }

    public function setPaysAffectation(?string $paysAffectation): static
    {
        $this->paysAffectation = $paysAffectation;

        return $this;
    }

    public function isStatut(): ?bool
    {
        return $this->statut;
    }

    public function setStatut(?bool $statut): static
    {
        $this->statut = $statut;

        return $this;
    }

    /**
     * @return Collection<int, Visite>
     */
    public function getVisites(): Collection
    {
        return $this->visites;
    }

    public function addVisite(Visite $visite): static
    {
        if (!$this->visites->contains($visite)) {
            $this->visites->add($visite);
            $visite->setGuide($this);
        }

        return $this;
    }

    public function removeVisite(Visite $visite): static
    {
        if ($this->visites->removeElement($visite)) {
            // set the owning side to null (unless already changed)
            if ($visite->getGuide() === $this) {
                $visite->setGuide(null);
            }
        }

        return $this;
    }

    public function __toString(): string
{
    return $this->prenom . ' ' . $this->nom; // Ou juste name() si tu préfères
}
}
