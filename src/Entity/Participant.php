<?php

namespace App\Entity;

use App\Repository\ParticipantRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: ParticipantRepository::class)]
#[UniqueEntity(fields: ['email', 'pseudo'], message: 'There is already an account with this email or pseudo')]
class Participant implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180, unique: true)]
    private ?string $email = null;

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

    #[ORM\Column(length: 10, unique: true)]
    private ?string $telephone = null;

    #[ORM\Column(length: 255, unique: true)]
    private ?string $pseudo = null;


    #[ORM\ManyToMany(targetEntity: Sortie::class, mappedBy: 'participants')]
    private Collection $sorties;

    #[ORM\OneToMany(mappedBy: 'organisateur', targetEntity: Sortie::class)]
    private Collection $sortiesOrganisees;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $last_activity_at = null;



    public function __construct()
    {
        $this->sorties = new ArrayCollection();
        $this->sortiesOrganisees = new ArrayCollection();

    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
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
        return (string) $this->pseudo;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): self
    {
        $this->nom = $nom;

        return $this;
    }

    public function getPrenom(): ?string
    {
        return $this->prenom;
    }

    public function setPrenom(string $prenom): self
    {
        $this->prenom = $prenom;

        return $this;
    }

    public function getTelephone(): ?string
    {
        return $this->telephone;
    }

    public function setTelephone(string $telephone): self
    {
        $this->telephone = $telephone;

        return $this;
    }

    public function getPseudo(): ?string
    {
        return $this->pseudo;
    }

    public function setPseudo(string $pseudo): self
    {
        $this->pseudo = $pseudo;

        return $this;
    }

    public function isActif(): ?bool
    {
        $delay = new \DateTime('2 minutes ago');

        return ( $this->getLastActivityAt() > $delay );
    }


    /**
     * @return Collection<int, Sortie>
     */
    public function getSorties(): Collection
    {
        return $this->sorties;
    }

    public function addSorty(Sortie $sorty): self
    {
        if (!$this->sorties->contains($sorty)) {
            $this->sorties->add($sorty);
            $sorty->addParticipant($this);
        }

        return $this;
    }

    public function removeSorty(Sortie $sorty): self
    {
        if ($this->sorties->removeElement($sorty)) {
            $sorty->removeParticipant($this);
        }

        return $this;
    }

    /**
     * @return Collection<int, Sortie>
     */
    public function getSortiesOrganisees(): Collection
    {
        return $this->sortiesOrganisees;
    }

    public function addSortiesOrganisee(Sortie $sortiesOrganisee): self
    {
        if (!$this->sortiesOrganisees->contains($sortiesOrganisee)) {
            $this->sortiesOrganisees->add($sortiesOrganisee);
            $sortiesOrganisee->setOrganisateur($this);
        }

        return $this;
    }

    public function removeSortiesOrganisee(Sortie $sortiesOrganisee): self
    {
        if ($this->sortiesOrganisees->removeElement($sortiesOrganisee)) {
            // set the owning side to null (unless already changed)
            if ($sortiesOrganisee->getOrganisateur() === $this) {
                $sortiesOrganisee->setOrganisateur(null);
            }
        }

        return $this;
    }

    public function getLastActivityAt(): ?\DateTimeImmutable
    {
        return $this->last_activity_at;
    }

    public function setLastActivityAt(?\DateTimeImmutable $last_activity_at): self
    {
        $this->last_activity_at = $last_activity_at;

        return $this;
    }

    /* nouvelle propriété upload */

    #[ORM\Column(type: 'string',nullable: true)]
    private string $brochureFilename ='';

    #[ORM\ManyToOne(inversedBy: 'participants')]
    private ?Campus $campus = null;

    #[ORM\ManyToOne(inversedBy: 'participant')]
    #[ORM\JoinColumn(nullable: false)]


    public function getBrochureFilename(): string
    {
        return $this->brochureFilename;
    }

    public function setBrochureFilename(string $brochureFilename)
    {
        $this->brochureFilename = $brochureFilename;

        return $this;
    }

    public function getCampus(): ?Campus
    {
        return $this->campus;
    }

    public function setCampus(?Campus $campus): self
    {
        $this->campus = $campus;

        return $this;
    }




}
