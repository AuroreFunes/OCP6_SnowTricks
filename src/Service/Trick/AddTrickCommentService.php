<?php

namespace App\Service\Trick;

use App\Entity\Trick;
use App\Entity\TrickComment;
use App\Entity\User;
use App\Service\ServiceHelper;
use Doctrine\Persistence\ManagerRegistry;

class AddTrickCommentService extends ServiceHelper
{

    public function __construct(ManagerRegistry $manager)
    {
        parent::__construct($manager);
    }


    // ============================================================================================
    // ENTRYPOINT
    // ============================================================================================
    public function addComment(TrickComment $comment, Trick $trick, User $user): self
    {
        $this->initHelper();

        $comment->setAuthor($user)
            ->setTrick($trick)
            ->setCreated(new \DateTime());
        
        try {
            $this->manager->persist($comment);
            $this->manager->flush();
        } catch (\Exception $e) {
            $this->errMessages->add("Erreur interne : " . $e);
            return $this;
        }

        $this->status = true;
        return $this;
    }

}