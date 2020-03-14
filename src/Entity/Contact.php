<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ContactRepository")
 */
class Contact
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
     * @Assert\Length(
     *      min = 2,
     *      max = 255,
     *      minMessage = "The name of the project must be at least {{ limit }} characters long",
     *      maxMessage = "The name of the project cannot be longer than {{ limit }} character"
     * )
     */
    private $senderFullName;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotNull
     * @Assert\Email
     */
    private $senderEmail;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotNull
     * @Assert\Length(
     *      min = 2,
     *      max = 255,
     *      minMessage = "The name of the project must be at least {{ limit }} characters long",
     *      maxMessage = "The name of the project cannot be longer than {{ limit }} character"
     * )
     */
    private $emailSubject;

    /**
     * @ORM\Column(type="text")
     * @Assert\NotNull
     */
    private $emailContent;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSenderFullName(): ?string
    {
        return $this->senderFullName;
    }

    public function setSenderFullName(string $senderFullName): self
    {
        $this->senderFullName = $senderFullName;

        return $this;
    }

    public function getSenderEmail(): ?string
    {
        return $this->senderEmail;
    }

    public function setSenderEmail(string $senderEmail): self
    {
        $this->senderEmail = $senderEmail;

        return $this;
    }

    public function getEmailSubject(): ?string
    {
        return $this->emailSubject;
    }

    public function setEmailSubject(string $emailSubject): self
    {
        $this->emailSubject = $emailSubject;

        return $this;
    }

    public function getEmailContent(): ?string
    {
        return $this->emailContent;
    }

    public function setEmailContent(string $emailContent): self
    {
        $this->emailContent = $emailContent;

        return $this;
    }
}
