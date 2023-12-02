<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\EducationRepository")
 */
class Education
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotNull
     * @Assert\Choice(
     *     choices = { "experience", "formation" },
     *     message = "Please, choose a valid category type."
     * )
     */
    private $category;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotNull
     * @Assert\Length(
     *      min = 2,
     *      max = 255,
     *      minMessage = "The job name must be at least {{ limit }} characters long",
     *      maxMessage = "The job name cannot be longer than {{ limit }} character"
     * )
     */
    private $name;

    /**
     * @ORM\Column(type="datetime")
     * @Assert\NotNull
     * @Assert\Type("\DateTimeInterface")
     */
    private $startDate;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     * @Assert\Type("\DateTimeInterface")
     */
    private $endDate;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $inProgress;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotNull
     * @Assert\Length(
     *      min = 2,
     *      max = 255,
     *      minMessage = "The corporation name must be at least {{ limit }} characters long",
     *      maxMessage = "The corporation name cannot be longer than {{ limit }} character"
     * )
     */
    private $corporationName;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $corporationSite;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $contractType;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $description;

    /**
     * @ORM\Column(type="json", nullable=true)
     */
    private $participateProjects = [];

    /**
     * @ORM\ManyToMany(targetEntity=Skills::class, mappedBy="educations")
     */
    private $skills;

    public function __construct()
    {
        $this->skills = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCategory(): ?string
    {
        return $this->category;
    }

    public function setCategory(string $category): self
    {
        $this->category = $category;

        return $this;
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

    public function getStartDate(): ?\DateTimeInterface
    {
        return $this->startDate;
    }

    public function setStartDate(\DateTimeInterface $startDate): self
    {
        $this->startDate = $startDate;

        return $this;
    }

    public function getEndDate(): ?\DateTimeInterface
    {
        return $this->endDate;
    }

    public function setEndDate(?\DateTimeInterface $endDate): self
    {
        $this->endDate = $endDate;

        return $this;
    }

    public function getInProgress(): ?bool
    {
        return $this->inProgress;
    }

    public function setInProgress(bool $inProgress): self
    {
        $this->inProgress = $inProgress;

        return $this;
    }

    public function getCorporationName(): ?string
    {
        return $this->corporationName;
    }

    public function setCorporationName(string $corporationName): self
    {
        $this->corporationName = $corporationName;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getParticipateProjects(): ?array
    {
        return $this->participateProjects;
    }

    public function setParticipateProjects(array $participateProjects): self
    {
        $this->participateProjects = $participateProjects;

        return $this;
    }

    public function getCorporationSite(): ?string
    {
        return $this->corporationSite;
    }

    public function setCorporationSite(string $corporationSite): self
    {
        $this->corporationSite = $corporationSite;

        return $this;
    }

    public function getContractType(): ?string
    {
        return $this->contractType;
    }

    public function setContractType(string $contractType): self
    {
        $this->contractType = $contractType;

        return $this;
    }

    /**
     * @return Collection<int, Skills>
     */
    public function getSkills(): Collection
    {
        return $this->skills;
    }

    public function addSkill(Skills $skill): self
    {
        if (!$this->skills->contains($skill)) {
            $this->skills[] = $skill;
            $skill->addEducation($this);
        }

        return $this;
    }

    public function removeSkill(Skills $skill): self
    {
        if ($this->skills->removeElement($skill)) {
            $skill->removeEducation($this);
        }

        return $this;
    }
}
