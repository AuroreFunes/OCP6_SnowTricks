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
            //dd("test", $oldToken, $user->getUserToken()); => ok
            try {
               $this->manager->remove($oldToken);   // erreur : "Notice: Undefined index: user"
               dd("test");
               $this->manager->flush();

               dd($oldToken, $user->getUserToken());
            } catch (\Exception $e) {
                dd("erreur token : ", $e, $oldToken->getUser(), $oldToken);
                return null;
            }
            
dd("token ok");
        }

        // create new token
        $token = new UserToken();
        $token->setCreated(new \DateTime());
        $token->setToken(bin2hex(openssl_random_pseudo_bytes(25)));
        $token->setUser($user);

        // return token content
        return $token;
    }
    
}