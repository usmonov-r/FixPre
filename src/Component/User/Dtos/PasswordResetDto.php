<?php

namespace App\Component\User\Dtos;

use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

class PasswordResetDto
{
    #[Assert\NotBlank]
    #[Groups(['password:reset'])]
    private ?string $token = null;

    #[Assert\NotBlank]
    #[Assert\Length(min: 6, minMessage: 'Password must be at least 6 characters')]
    #[Groups(['password:reset'])]
    private ?string $newPassword = null;

    public function getToken(): ?string
    {
        return $this->token;
    }

    public function setToken(string $token): void
    {
        $this->token = $token;
    }

    public function getNewPassword(): ?string
    {
        return $this->newPassword;
    }

    public function setNewPassword(string $newPassword): void
    {
        $this->newPassword = $newPassword;
    }
}
