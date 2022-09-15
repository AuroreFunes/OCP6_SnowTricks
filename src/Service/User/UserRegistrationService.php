<?php

namespace App\Service\User;

use App\Entity\User;
use App\Service\ServiceHelper;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserRegistrationService extends ServiceHelper
{

    private const INTERNAL_ERROR = "Une erreur interne s'est produite. Merci de réessayer ultérieurement.";

    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(
        ManagerRegistry $manager,
        UserPasswordHasherInterface $passwordHasher
    ) {
        parent::__construct($manager);

        $this->passwordHasher = $passwordHasher;
    }

    // ========================================================================================
    // ENTRYPOINT
    // ========================================================================================
    public function createUser(User $user) :User
    {
        // the parameters have already been checked by the assertions in the form

        $user->setPassword($this->passwordHasher->hashPassword($user, $user->getPassword()));
        $user->setIsActive(false);
        $user->setCreatedAt(new \DateTime());

        try {
            $this->manager->persist($user);
            $this->manager->flush();
        } catch (\Exception $e) {
            $this->errMessages->add(self::INTERNAL_ERROR);
            return null;
        }

        $this->status = true;
        return $user;
    }

}