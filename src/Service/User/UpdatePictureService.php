<?php

namespace App\Service\User;

use App\Entity\User;
use App\Service\ServiceHelper;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UpdatePictureService extends ServiceHelper
{

    private const INTERNAL_ERROR = "Une erreur interne s'est produite. Merci de réessayer ultérieurement.";

    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(
        ManagerRegistry $manager,
        UserPasswordHasherInterface $passwordHasher
    ) {
        parent::__construct($manager);
        
        $this->manager = $manager;
        $this->passwordHasher = $passwordHasher;
        
    }

    // ========================================================================================
    // ENTRYPOINT
    // ========================================================================================
    public function updateUserPicture(User $user)
    {

    }


    // ========================================================================================
    // CHECK PARAMETERS
    // ========================================================================================
    protected function checkFile()
    {

    }

}