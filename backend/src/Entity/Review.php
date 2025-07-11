<?php

namespace App\Entity;

use App\Repository\ReviewRepository;
use App\Enum\ReviewStatus;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\MaxDepth;

#[ORM\Entity(repositoryClass: ReviewRepository::class)]
class Review
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['review:read', 'review:write'])]
    private ?int $id = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Groups(['review:read', 'review:write'])]
    private ?string $comment = null;

    #[ORM\Column]
    #[Groups(['review:read', 'review:write'])]
    private ?int $ratting = null;

    #[ORM\Column(type: 'string', enumType: ReviewStatus::class)]
    #[Groups(['review:read', 'review:write'])]
    private ReviewStatus $status;

    // User who left the review (author)
    #[ORM\ManyToOne(inversedBy: 'reviews')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['review:read'])]
    #[MaxDepth(1)]
    private ?User $user = null;

    // User who receives the review (target, e.g., driver)
    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: "reviewed_user_id", referencedColumnName: "id", nullable: false)]
    #[Groups(['review:read', 'review:write'])]
    #[MaxDepth(1)]
    private ?User $reviewedUser = null;

    #[ORM\Column]
    #[Groups(['review:read'])]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['review:read'])]
    private ?\DateTimeImmutable $updatedAt = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function setComment(string $comment): static
    {
        $this->comment = $comment;
        return $this;
    }

    public function getRatting(): ?int
    {
        return $this->ratting;
    }

    public function setRatting(int $ratting): static
    {
        $this->ratting = $ratting;
        return $this;
    }

    public function getStatus(): ReviewStatus
    {
        return $this->status;
    }

    public function setStatus(ReviewStatus $status): self
    {
        $this->status = $status;
        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;
        return $this;
    }

    public function getReviewedUser(): ?User
    {
        return $this->reviewedUser;
    }

    public function setReviewedUser(?User $reviewedUser): static
    {
        $this->reviewedUser = $reviewedUser;
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeImmutable $updatedAt): static
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }
}
