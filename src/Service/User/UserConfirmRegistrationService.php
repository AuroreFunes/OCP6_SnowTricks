<?php

namespace App\Service\User;

use App\Entity\User;
use App\Service\ServiceHelper;
use Doctrine\Persistence\ManagerRegistry;


class UserConfirmRegistrationService extends ServiceHelper
{

    protected const ERR_USER_NOT_FOUND  = "L'utilisateur n'a pas été trouvé ou est invalide.";
    protected const ERR_TOKEN_NOT_FOUND = "Le jeton n'a pas été trouvé.";
    protected const ERR_TOKEN_DOESNT_MATCH = "Le jeton ne correspond pas à l'utilisateur.";


    public function __construct(
        ManagerRegistry $manager
    ) {
        parent::__construct($manager);
    }

    // ========================================================================================
    // ENTRYPOINT
    // ========================================================================================
    public function activateUser(?User $user, string $token): self
    {
        $this->initHelper();
        $this->functArgs->set('user', $user);
        $this->functArgs->set('token', $token);

        if (false === $this->checkToken()) {
            return $this;
        }

        if (false === $this->makeActivation()) {
            return $this;
        }

        $this->status = true;
        return $this;
    }

    // ========================================================================================
    // JOBS
    // ========================================================================================
    protected function makeActivation()
    {
        // activate user account
        $this->functArgs->get('user')->setIsActive(true);
        
        try {
            // remove used token
            $this->manager->remove($this->functArgs->get('user')->getUserToken());
            $this->manager->persist($this->functArgs->get('user'));
            $this->manager->flush();
        } catch (\Exception $e) {
            $this->errMessages->add("Erreur interne : " . $e->getMessage());
            return false;
        }
        
        return true;
    }

    // ========================================================================================
    // CHECK PARAMETERS
    // ========================================================================================
    protected function checkToken(): bool
    {
        if (null === $this->functArgs->get('user')) {
            $this->errMessages->add(self::ERR_USER_NOT_FOUND);
            return false;
        }

        if (null === $this->functArgs->get('user')->getUserToken()) {
            $this->errMessages->add(self::ERR_TOKEN_NOT_FOUND);
            return false;
        }

        if ($this->functArgs->get('token') !== $this->functArgs->get('user')->getUserToken()->getToken()) {
            $this->errMessages->add(self::ERR_TOKEN_DOESNT_MATCH);
            return false;
        }

        return true;
    }

}