<?php

namespace App\Service\User\Tools;

use App\Entity\User;
use App\Entity\UserToken;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\ObjectManager;

class UserTokenManagement
{
    private ObjectManager $manager;

    public function __construct(ManagerRegistry $manager) {
        $this->manager = $manager->getManager();
    }

    public function createToken(User $user): ?UserToken
    {
        // delete old token
        if (null !== $oldToken = $user->getUserToken()) {
            try {
               $this->manager->remove($oldToken);
               $this->manager->flush();
            } catch (\Exception $e) {
                return null;
            }
        }

        // create new token
        $token = new UserToken();
        $token->setCreated(new \DateTime());
        $token->setToken(bin2hex(openssl_random_pseudo_bytes(25)));
        //$token->setUser($user);
        $user->addToken($token);

        // save token
        try {
            $this->manager->persist($token);
        } catch (\Exception $e) {
            return null;
        }

        // return token
        return $token;
    }
    
}