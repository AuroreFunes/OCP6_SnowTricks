<?php

namespace App\Service\User;

use App\Entity\User;
use App\Service\ServiceHelper;
use App\Service\User\Tools\UserTokenManagement;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserRegistrationService extends ServiceHelper
{

    private const INTERNAL_ERROR = "Une erreur interne s'est produite. Merci de réessayer ultérieurement.";

    protected UserPasswordHasherInterface   $passwordHasher;
    protected UserTokenManagement           $tokenManager;

    public function __construct(
        ManagerRegistry $manager,
        UserPasswordHasherInterface $passwordHasher,
        UserTokenManagement $tokenManager
    ) {
        parent::__construct($manager);

        $this->passwordHasher   = $passwordHasher;
        $this->tokenManager     = $tokenManager;
    }

    // ========================================================================================
    // ENTRYPOINT
    // ========================================================================================
    public function createUser(User $user): self
    {
        // the parameters have already been checked by the assertions in the form

        // create user
        $user->setPassword($this->passwordHasher->hashPassword($user, $user->getPassword()));
        $user->setIsActive(false);
        $user->setCreatedAt(new \DateTime());

        // create token
        if (null === $token = $this->tokenManager->createToken($user)) {
            $this->errMessages->add(self::INTERNAL_ERROR);dd("erreur ici");
            return $this;
        }

        // save user and token
        try {
            $this->manager->persist($user);
            $this->manager->flush();
        } catch (\Exception $e) {
            $this->errMessages->add(self::INTERNAL_ERROR);
            return $this;
        }

        $this->functResult->set('user', $user);

        $this->status = true;
        return $this;
    }

}