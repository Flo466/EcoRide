<?php

// src/Service/TokenService.php
namespace App\Service;

use App\Entity\User;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class TokenService
{
    public function generateApiToken(): string
    {
        return bin2hex(random_bytes(50));
    }

    public function setApiToken(User $user): void
    {
        $user->setApiToken($this->generateApiToken());
    }

    public function validateApiToken(User $user, string $providedToken): bool
    {
        if ($user->getApiToken() === $providedToken) {
            return true;
        }

        throw new CustomUserMessageAuthenticationException('Invalid API Token.');
    }
}
