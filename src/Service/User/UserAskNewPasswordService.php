<?php

namespace App\Service\User;

use App\Entity\User;
use App\Service\SendMailServiceHelper;
use App\Service\User\Tools\UserTokenManagement;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mailer\MailerInterface;


class UserAskNewPasswordService extends SendMailServiceHelper
{
    protected const ERR_USER_NOT_FOUND = "Il n'existe pas d'utilisateur avec cet e-mail.";

    protected UserTokenManagement   $tokenManager;
    protected MailerInterface       $mailer;

    public function __construct(
        ManagerRegistry $manager,
        MailerInterface  $mailer,
        UserTokenManagement $tokenManager
    ) {
        parent::__construct($manager);

        $this->mailer       = $mailer;
        $this->tokenManager = $tokenManager;
    }

    // ============================================================================================
    // ENTRYPOINT
    // ============================================================================================
    public function sendToken(string $email): self
    {
        $this->initHelper();
        $this->functArgs->set('email', $email);

        if (false === $this->checkParameters()) {
            // do not show the error to the user !!!
            $this->status = true;
            return $this;
        }

        if (false === $this->sendMail()) {
            return $this;
        }

        $this->status = true;
        return $this;
    }

    // ============================================================================================
    // JOBS
    // ============================================================================================
    protected function sendMail()
    {
        /** @var UserToken $token */
        $token = $this->tokenManager->createToken($this->functResult->get('user'));

        try {
            $this->manager->persist($token);
            $this->manager->flush();
        } catch (\Exception $e) {
            $this->errMessages->add("Erreur interne : " . $e);
            return false;
        }

        $path = "test" . "/" . $this->functResult->get('user')->getId() . "/" . $token->getToken();

        $message = (new Email())
            ->from($_ENV['ADMIN_EMAIL'])
            ->to($this->functResult->get('user')->getEmail())
            ->subject('Réinitialisation de votre mot de passe')
            ->html($this->twig->render('email/userForgottenPassword.html.twig', [
                    'username' => $this->functResult->get('user')->getUsername(),
                    'fullPath' => $path
                ]),
            );

        $this->mailer->send($message);

        return true;
    }


    // ============================================================================================
    // CHECK PARAMETERS
    // ============================================================================================
    protected function checkParameters(): bool
    {
        $user = $this->manager->getRepository(User::class)->findOneBy([
            'email' => $this->functArgs->get('email')
        ]);

        if (null === $user) {
            $this->errMessages->add(self::ERR_USER_NOT_FOUND);
            return false;
        }

        $this->functResult->set('user', $user);
        return true;
    }

}