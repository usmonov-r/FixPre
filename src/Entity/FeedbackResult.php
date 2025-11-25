<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Patch;
use App\Repository\FeedbackResultRepository;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: FeedbackResultRepository::class)]
#[ApiResource(
    operations : [
        new Get(),
        new GetCollection(),
        new Post(),
        new Patch(
            security: 'is_granted("ROLE_ADMIN")'
        ),
        new Delete(
            security: 'is_granted("ROLE_ADMIN")'
        )
    ],
    normalizationContext: ['groups' => ['feedback:read']],
    denormalizationContext: ['groups' => ['feedback:write']]
)]
class FeedbackResult
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['feedback:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['feedback:read'])]
    private ?string $job_id = null;

    #[ORM\Column(length: 50)]
    #[Groups(['feedback:read', 'feedback:write'])]
    private ?string $status = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['feedback:read', 'feedback:write'])]
    private ?array $feedback = null;

    #[ORM\Column]
    #[Groups(['feedback:read'])]
    private ?DateTimeImmutable $created_at = null;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'feedbackResults')]
    #[ORM\JoinColumn(nullable: true)]
    private ?User $user = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['feedback:read', 'feedback:write'])]
    private ?int $overallScore = null;

    public function __construct()
    {
        $this->created_at = new DateTimeImmutable();
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(User $user): static
    {
        $this->user = $user;
        return $this;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getJobId(): ?string
    {
        return $this->job_id;
    }

    public function setJobId(string $job_id): static
    {
        $this->job_id = $job_id;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function getFeedback(): ?array
    {
        return $this->feedback;
    }

    public function setFeedback(?array $feedback): static
    {
        $this->feedback = $feedback;

        return $this;
    }

    public function getCreatedAt(): ?DateTimeImmutable
    {
        return $this->created_at;
    }

    public function setCreatedAt(DateTimeImmutable $created_at): static
    {
        $this->created_at = $created_at;

        return $this;
    }

    public function getOverallScore(): ?int
    {
        return $this->overallScore;
    }

    public function setOverallScore(?int $overallScore): static
    {
        $this->overallScore = $overallScore;

        return $this;
    }
}
