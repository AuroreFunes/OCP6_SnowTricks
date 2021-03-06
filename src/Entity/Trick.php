<?php

namespace App\Entity;

use App\Repository\TrickRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=TrickRepository::class)
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
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\Column(type="text")
     */
    private $description;

    /**
     * @ORM\ManyToOne(targetEntity=TrickGroup::class, inversedBy="tricks")
     * @ORM\JoinColumn(nullable=false)
     */
    private $group;

    /**
     * @ORM\OneToMany(targetEntity=TrickComment::class, mappedBy="trick", orphanRemoval=true, cascade={"persist", "remove"})
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
}
