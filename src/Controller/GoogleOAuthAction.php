<?php

namespace App\Controller;

use App\Controller\Base\AbstractController;
use App\Entity\User;
use App\Repository\UserRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

class GoogleOAuthAction extends AbstractController
{
    #[Route('/api/auth/google', name: 'api_auth_google', methods: ['POST'])]
    public function __invoke(
        Request $request,
        ClientRegistry $clientRegistry,
        EntityManagerInterface $entityManager,
        UserRepository $userRepository,
        JWTTokenManagerInterface $jwtManager,
        UserPasswordHasherInterface $passwordHasher
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);
        $code = $data['code'] ?? null;

        if (!$code) {
            return $this->json(['error' => 'No authorization code provided'], 400);
        }

        $client = $clientRegistry->getClient('google');

        try {
            $accessToken = $client->getOAuth2Provider()->getAccessToken('authorization_code', [
                'code' => $code,
                'redirect_uri' => 'https://fixpre.kengroq.uz/auth/callback'
            ]);

            $googleUser = $client->fetchUserFromToken($accessToken);
            $email = $googleUser->getEmail();

            $user = $userRepository->findOneByEmail($email);

            if (!$user) {
                $user = new User();
                $user->setEmail($email);

                $randomPwd = bin2hex(random_bytes(16));
                $hashedPwd = $passwordHasher->hashPassword($user, $randomPwd);

                $user->setPassword($hashedPwd);
                $user->setRoles(['ROLE_USER']);
                $user->setCreatedAt(new DateTime());

                $entityManager->persist($user);
                $entityManager->flush();
            }

            $token = $jwtManager->create($user);

            return $this->json([
                'token' => $token,
                'user' => [
                    'email' => $user->getEmail(),
                    'id' => $user->getId()
                ]
            ]);

        } catch (IdentityProviderException $e) {
            return $this->json(['error' => 'Google Authentication failed: ' . $e->getMessage()], 400);
        } catch (Exception $e) {
            return $this->json(['error' => 'An error occured: ' . $e->getMessage()], 500);
        }
    }
}
