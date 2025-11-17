<?php

namespace App\Component\User\Dtos;

use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;
class PasswordRequestDto
{
    #[Assert\NotBlank]
    #[Assert\Email]
    #[Groups(['password:request'])]
    private ?string $email = null;

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail( string $email): void
    {
        $this->email = $email;
    }
}
