<?php

namespace App\Controller;

use App\Component\User\Dtos\PasswordResetDto;
use App\Controller\Base\AbstractController;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class PasswordResetAction extends AbstractController
{
    public function __invoke(
        PasswordResetDto $data,
        UserRepository $userRepository,
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher
    ): JsonResponse
    {
        $token = $data->getToken();
        $user = $userRepository->findOneBy(['passwordResetToken' => $token]);
//        var_dump($user);
        if(!$user || $user->getPasswordResetExpiresAt() < new \DateTimeImmutable()){
            throw new BadRequestHttpException('This link is invalid or has expired.');
        }

        $hashedPassword = $passwordHasher->hashPassword($user, $data->getNewPassword());
        $user->setPassword($hashedPassword);

        $user->setPasswordResetToken(null);
        $user->setPasswordResetExpiresAt(null);

        $entityManager->flush();

        return $this->json(['message' => 'Password has been  reset successfully']);
    }

}
