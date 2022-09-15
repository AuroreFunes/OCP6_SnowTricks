<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;


/**
 * @ORM\Entity(repositoryClass=UserRepository::class)
 * @UniqueEntity(
 *  fields={"email"},
 *  message="Cette adresse e-mail est déjà utilisée."
 * )
 * @UniqueEntity(
 *  fields={"username"},
 *  message="Ce nom d'utilisateur est déjà utilisé."
 * )
 */
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=100)
     * @Assert\NotBlank(
     *      message = "Vous devez choisir un nom d'utilisateur."
     * )
     * @Assert\Length(
     *      min = 3,
     *      max = 50,
     *      minMessage = "Le nom d'utilisateur doit comporter entre 3 et 50 caractères.",
     *      maxMessage = "Le nom d'utilisateur doit comporter entre 3 et 50 caractères."
     * )
     */
    private $username;

    /**
     * @ORM\Column(type="string", length=255)
     *  @Assert\NotBlank(
     *      message = "Vous devez entrer votre adresse e-mail."
     * )
     * @Assert\Email(
     *      message = "L'adresse e-mail n'est pas valide."
     * )
     * @Assert\Length(
     *      max = 254,
     *      maxMessage = "L'adresse email ne peut pas contenir plus de {{ limit }} caractères."
     * )
     */
    private $email;

    /**
     * @var string The hashed password
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank(
     *      message = "Vous devez choisir un mot de passe."
     * )
     * @Assert\Length(
     *      min="8",
     *      max="254",
     *      minMessage="Le mot de passe doit faire entre 8 et 254 caractères.",
     *      maxMessage="Le mot de passe doit faire entre 8 et 254 caractères."
     * )
     * @Assert\Regex(
     *     pattern = "^(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9])^",
     *     match = true,
     *     message = "Le mot de passe doit contenir au moins une minuscule, une majuscule et un chiffre."
     * )
     */
    private $password;

    /**
     * @var string "password_confirm" is not in database, it's only use in registration form
     * @Assert\EqualTo(
     *      propertyPath="password_confirm",
     *      message="Les mots de passe doivent être identiques."
     * )
     */
    private $password_confirm;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isActive;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $profilePicture;

    /**
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $updatedAt;

    /**
     * @ORM\OneToOne(targetEntity=UserToken::class, mappedBy="user", cascade={"persist", "remove"})
     */
    private $userToken;

    /**
     * @ORM\OneToMany(targetEntity=TrickComment::class, mappedBy="user", orphanRemoval=true)
     */
    private $trickComments;

    /**
     * @ORM\OneToMany(targetEntity=TrickHistory::class, mappedBy="author", orphanRemoval=true)
     */
    private $trickHistories;

    // not necessary on our database because we have only "user role" and any admin role
    private $roles = [];

    public function __construct()
    {
        $this->trickComments = new ArrayCollection();
        $this->trickHistories = new ArrayCollection();

        $this->roles[] = 'ROLE_USER';
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): self
    {
        $this->username = $username;

        return $this;
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

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    public function isIsActive(): ?bool
    {
        return $this->isActive;
    }

    public function setIsActive(bool $isActive): self
    {
        $this->isActive = $isActive;

        return $this;
    }

    public function getProfilePicture(): ?string
    {
        return $this->profilePicture;
    }

    public function setProfilePicture(?string $profilePicture): self
    {
        $this->profilePicture = $profilePicture;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeInterface $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    public function getUserToken(): ?UserToken
    {
        return $this->userToken;
    }

    public function setUserToken(UserToken $userToken): self
    {
        // set the owning side of the relation if necessary
        if ($userToken->getUser() !== $this) {
            $userToken->setUser($this);
        }

        $this->userToken = $userToken;

        return $this;
    }

    /**
     * @return Collection<int, TrickComment>
     */
    public function getTrickComments(): Collection
    {
        return $this->trickComments;
    }

    public function addTrickComment(TrickComment $trickComment): self
    {
        if (!$this->trickComments->contains($trickComment)) {
            $this->trickComments[] = $trickComment;
            $trickComment->setAuthor($this);
        }

        return $this;
    }

    public function removeTrickComment(TrickComment $trickComment): self
    {
        if ($this->trickComments->removeElement($trickComment)) {
            // set the owning side to null (unless already changed)
            if ($trickComment->getAuthor() === $this) {
                $trickComment->setAuthor(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, TrickHistory>
     */
    public function getTrickHistories(): Collection
    {
        return $this->trickHistories;
    }

    public function addTrickHistory(TrickHistory $trickHistory): self
    {
        if (!$this->trickHistories->contains($trickHistory)) {
            $this->trickHistories[] = $trickHistory;
            $trickHistory->setAuthor($this);
        }

        return $this;
    }

    public function removeTrickHistory(TrickHistory $trickHistory): self
    {
        if ($this->trickHistories->removeElement($trickHistory)) {
            // set the owning side to null (unless already changed)
            if ($trickHistory->getAuthor() === $this) {
                $trickHistory->setAuthor(null);
            }
        }

        return $this;
    }

    // "password_confirm" is not in database, it's only use in registration form
    public function getPasswordConfirm(): ?string
    {
        return $this->password_confirm;
    }

    public function setPasswordConfirm(?string $password_confirm): self
    {
        $this->password_confirm = $password_confirm;

        return $this;
    }


    // ============================================================================================
    // USER INTERFACE
    // ============================================================================================
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    public function getRoles(): array
    {
        if (empty($this->roles)) {
            return ['ROSE_USER'];
        }

        return array_unique($this->roles);
    }
    
    public function eraseCredentials()
    {
        
    }

    public function getSalt() : ?string
    {
        return null;
    }

}
