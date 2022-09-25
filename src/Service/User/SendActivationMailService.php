<?php

namespace App\Service\User;

use App\Entity\User;
use App\Service\SendMailServiceHelper;
use App\Service\User\Tools\UserTokenManagement;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Doctrine\Persistence\ManagerRegistry;

class SendActivationMailService extends SendMailServiceHelper
{

    private const ERR_INVALID_USER = "L'utilisateur est invalide ou n'a pas été correctement créé.";
    private const ERR_USER_DOESNT_HAVE_TOKEN = "Il n'y a pas de jeton pour cet utilisateur.";
    private const ERR_USER_ALREADY_ACTIVE = "L'utilisateur est déjà actif.";

    protected UserTokenManagement   $tokenManager;
    protected MailerInterface       $mailer;


    public function __construct(
        ManagerRegistry $manager,
        UserTokenManagement $tokenManager,
        MailerInterface $mailer
    ) {
        parent::__construct($manager);

        $this->tokenManager = $tokenManager;
        $this->mailer       = $mailer;
    }


    // ========================================================================================
    // ENTRYPOINT
    // ========================================================================================
    public function sendMail(User $user): self
    {
        $this->functArgs->set('user', $user);

        if (false === $this->checkParameters()) {
            return $this;
        }

        $this->createMail();

        $this->status = true;
        return $this;
    }

    // ========================================================================================
    // PRIVATE JOBS
    // ========================================================================================
    protected function createMail()
    {

        $path = "test" . "/" . $this->functArgs->get('user')->getId() . "/" . $this->functArgs->get('user')->getUserToken()->getToken();

        $message = (new Email())
            ->from($_ENV['ADMIN_EMAIL'])
            ->to($this->functArgs->get('user')->getEmail())
            ->subject('Confirmation de votre inscription')
            ->html($this->twig->render('email/userConfirmRegistration.html.twig', [
                    'username' => $this->functArgs->get('user')->getUsername(),
                    'fullPath' => $path
                ]),
            );

        /*
        $message = (new Email())
            ->from($_ENV['ADMIN_EMAIL'])
            ->to($this->functArgs->get('user')->getEmail())
            ->subject('Confirmation de votre inscription')
            ->html($this->twig->render('email/userConfirmRegistration.html.twig', [
                    'username' => $this->functArgs->get('user')->getUsername(),
                    'id' => $this->functArgs->get('user')->getId(),
                    'token' => $this->functArgs->get('user')->getUserToken()->getToken()
                ]),
            );
            */

        $this->mailer->send($message);
    }

    // ========================================================================================
    // CHECKING JOBS
    // ========================================================================================
    private function checkParameters(): bool
    {
        // the user needs to have an ID
        if (null === $this->functArgs->get('user')->getId()) {
            $this->errMessages->add(self::ERR_INVALID_USER);
            return false;
        }

        // the user must be inactive
        if (true === $this->functArgs->get('user')->getIsActive()) {
            $this->errMessages->add(self::ERR_USER_ALREADY_ACTIVE);
            return false;
        }

        // the user must have a token
        if (null === $this->functArgs->get('user')->getUserToken()) {
            $this->errMessages->add(self::ERR_USER_DOESNT_HAVE_TOKEN);
            return false;
        }

        return true;
    }
}