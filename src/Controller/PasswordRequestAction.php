<?php

namespace App\Controller;

use App\Component\User\Dtos\PasswordRequestDto;
use App\Controller\Base\AbstractController;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class PasswordRequestAction extends  AbstractController
{
    public function __invoke(
        PasswordRequestDto $data,
        UserRepository $userRepository,
        EntityManagerInterface $entityManager,
        MailerInterface $mailer,
        LoggerInterface $logger
    ): JsonResponse {
        $user = $userRepository->findOneBy(['email' => $data->getEmail()]);
        if($user) {
            try{
                $token = bin2hex(random_bytes(32));
                $expires = new \DateTime('+1 hour');

                $user->setPasswordResetToken($token);
                $user->setPasswordResetExpiresAt($expires);

                $entityManager->flush();

                // SEND RESET EMAIL
                $resetUrl = 'http://localhost:5173/reset-password?token=' . $token;

                $email = (new Email())
                    ->from('usmonovraxmat600@gmail.com')
                    ->to($user->getEmail())
                    ->subject('Your FixPre Password Reset Request')
                    ->html(
                        "<p>Hi,</p>" .
                        "<p>Someone requested a password reset for your account.</p>" .
                        "<p>If this was you, please click the link below to set a new password. This link will expire in 1 hour.</p>" .
                        "<p><a href='$resetUrl'>Click here to reset your password</a></p>" .
                        "<p>If you did not request this, you can safely ignore this email.</p>"
                    );
                $mailer->send($email);

            }catch (\Exception $e){
                // log error but don't expose it to the user's email msg
                $logger->error('Failed to send password reset email '. $e->getMessage());
            }
        }else{
            return $this->json([
                'message' => 'User does not exist in database'
            ]);
        }
        return $this->json([
            'message' => 'If an account with that email exist, a password reset link has been sent'
        ]);
    }
}
