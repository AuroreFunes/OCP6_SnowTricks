<?php

namespace App\Entity;

use App\Repository\TrickRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity(repositoryClass=TrickRepository::class)
 * @UniqueEntity(
 *  fields={"name"},
 *  message="Une figure du même nom existe déjà."
 * )
 */
class Trick
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255, unique=true)
     * @Assert\Length(
     *      min=2,
     *      minMessage="Le nom doit contenir au moins deux caractères.",
     *      max=255,
     *      maxMessage="Le nom ne peut pas dépasser 255 caractères.")
     */
    private $name;

    /**
     * @ORM\Column(type="text")
     * @Assert\Length(
     *      min=20,
     *      minMessage="La description doit comporter au moins 20 caractères.")
     */
    private $description;

    /**
     * @ORM\ManyToOne(targetEntity=TrickGroup::class, inversedBy="tricks")
     * @ORM\JoinColumn(nullable=false)
     */
    private $group;

    /**
     * @ORM\OneToMany(targetEntity=TrickComment::class, mappedBy="trick", orphanRemoval=true, cascade={"persist", "remove"})
     * @ORM\OrderBy({"createdAt" = "DESC"})
     */
    private $trickComments;

    /**
     * @ORM\OneToMany(targetEntity=TrickImage::class, mappedBy="trick", cascade={"persist", "remove"})
     */
    private $trickImages;

    /**
     * @ORM\OneToMany(targetEntity=TrickVideo::class, mappedBy="trick", cascade={"persist", "remove"})
     */
    private $trickVideos;

    /**
     * @ORM\OneToMany(targetEntity=TrickHistory::class, mappedBy="trick", orphanRemoval=true, cascade={"persist", "remove"})
     */
    private $trickHistories;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $slug;

    public function __construct()
    {
        $this->trickComments = new ArrayCollection();
        $this->trickImages = new ArrayCollection();
        $this->trickVideos = new ArrayCollection();
        $this->trickHistories = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getGroup(): ?TrickGroup
    {
        return $this->group;
    }

    public function setGroup(?TrickGroup $group): self
    {
        $this->group = $group;

        return $this;
    }

    /**
     * @return Collection<int, TrickComment>
     */
    public function getComments(): Collection
    {
        return $this->trickComments;
    }

    public function addComment(TrickComment $trickComment): self
    {
        if (!$this->trickComments->contains($trickComment)) {
            $this->trickComments[] = $trickComment;
            $trickComment->setTrick($this);
        }

        return $this;
    }

    public function removeComment(TrickComment $trickComment): self
    {
        if ($this->trickComments->removeElement($trickComment)) {
            // set the owning side to null (unless already changed)
            if ($trickComment->getTrick() === $this) {
                $trickComment->setTrick(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, TrickImage>
     */
    public function getImages(): Collection
    {
        return $this->trickImages;
    }

    public function addImage(TrickImage $trickImage): self
    {
        if (!$this->trickImages->contains($trickImage)) {
            $this->trickImages[] = $trickImage;
            $trickImage->setTrick($this);
        }

        return $this;
    }

    public function removeImage(TrickImage $trickImage): self
    {
        if ($this->trickImages->removeElement($trickImage)) {
            // set the owning side to null (unless already changed)
            if ($trickImage->getTrick() === $this) {
                $trickImage->setTrick(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, TrickVideo>
     */
    public function getVideos(): Collection
    {
        return $this->trickVideos;
    }

    public function addVideo(TrickVideo $trickVideo): self
    {
        if (!$this->trickVideos->contains($trickVideo)) {
            $this->trickVideos[] = $trickVideo;
            $trickVideo->setTrick($this);
        }

        return $this;
    }

    public function removeVideo(TrickVideo $trickVideo): self
    {
        if ($this->trickVideos->removeElement($trickVideo)) {
            // set the owning side to null (unless already changed)
            if ($trickVideo->getTrick() === $this) {
                $trickVideo->setTrick(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, TrickHistory>
     */
    public function getHistories(): Collection
    {
        return $this->trickHistories;
    }

    public function addHistory(TrickHistory $trickHistory): self
    {
        if (!$this->trickHistories->contains($trickHistory)) {
            $this->trickHistories[] = $trickHistory;
            $trickHistory->setTrick($this);
        }

        return $this;
    }

    public function removeHistory(TrickHistory $trickHistory): self
    {
        if ($this->trickHistories->removeElement($trickHistory)) {
            // set the owning side to null (unless already changed)
            if ($trickHistory->getTrick() === $this) {
                $trickHistory->setTrick(null);
            }
        }

        return $this;
    }

    // ========================================================================================
    // PICTURES
    // ========================================================================================
    public function getDefaultImage() :?TrickImage
    {
        foreach ($this->trickImages as $image) {
            if ($image->getIsDefault()) {
                return $image;
            }
        }
        return $this->getImages()[0];
    }

    public function setDefaultImage(TrickImage $defaultImage, bool $add = false)
    {
        $oldImage = $this->getDefaultImage();
        if (null !== $oldImage) {
            $oldImage->setIsDefault(false);
        }

        $defaultImage->setIsDefault(true);

        if ($add) {
            $this->addImage($defaultImage);
        }

        return $this;
    }

    // ========================================================================================
    // HISTORY
    // ========================================================================================
    public function getCreatedAt() :\DateTime
    {
        $history = $this->getHistories()->get(0);

        foreach($this->getHistories() as $currentHistory) {
            if ($history->getDate() > $currentHistory->getDate()) {
                $history = $currentHistory;
            }
        }

        return $history->getDate();
    }

    public function getUpdatedAt() :\DateTime
    {
        $history = $this->getHistories()->get(0);

        foreach($this->getHistories() as $currentHistory) {
            if ($history->getDate() < $currentHistory->getDate()) {
                $history = $currentHistory;
            }
        }

        return $history->getdate();
    }

    // ========================================================================================
    // SLUG
    // ========================================================================================
    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): self
    {
        $this->slug = $slug;
        return $this;
    }
}
