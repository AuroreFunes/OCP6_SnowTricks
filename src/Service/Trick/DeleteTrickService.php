<?php

namespace App\Service\Trick;

use App\Entity\Trick;
use App\Entity\TrickGroup;
use App\Entity\TrickHistory;
use App\Entity\TrickImage;
use App\Entity\TrickVideo;
use App\Entity\User;
use App\Repository\TrickRepository;
use App\Service\ServiceHelper;
use Doctrine\Persistence\ManagerRegistry;

class DeleteTrickService extends ServiceHelper
{
    protected const ERR_TRICK_EMPTY     = "La figure n'est pas définie.";
    protected const ERR_USER_UNKNOWN    = "L'utilisateur doit être connecté.";
    protected const ERR_DELETE_IMAGES   = "Toutes les images n'ont pas pu être supprimées.";
    protected const ERR_DELETE_TRICK    = "La figure n'a pas pu être supprimée.";

    protected string $projectDir;
    protected TrickRepository $trickRepository;
    protected int $responseHttpCode;


    public function __construct(ManagerRegistry $manager, TrickRepository $trickRepository, string $projectDir)
    {
        parent::__construct($manager, $projectDir);

        $this->trickRepository  = $trickRepository;
        $this->projectDir       = $projectDir . '\public\img\tricks\\';
        $this->responseHttpCode = 200;
    }

    // ============================================================================================
    // ENTRYPOINT
    // ============================================================================================
    public function deleteTrick(?User $user, ?Trick $trick) :self
    {
        $this->initHelper();

        // save parameters
        $this->functArgs->set('user', $user);
        $this->functArgs->set('trick', $trick);

        if (false === $this->checkParameters()) {
            $this->responseHttpCode = 400;
            return $this;
        }

        $fullSuccess = true;

        if (false === $this->deleteImages()) {
            $this->functResult->add(self::ERR_DELETE_IMAGES);
            $fullSuccess = false;
            $this->responseHttpCode = 206;
        }

        if (false === $this->makeDelete()) {
            $this->functResult->add(self::ERR_DELETE_TRICK);
            $this->responseHttpCode = 500;
            return $this;
        }

        $this->status = $fullSuccess;

        return $this;
    }

    // ============================================================================================
    // JOBS
    // ============================================================================================
    protected function deleteImages(): bool
    {
        // delete images
        $success = true;
        foreach($this->functArgs->get('trick')->getImages() as $image) {
            if (file_exists($this->projectDir . $image->getPath())) {
                if (false === unlink($this->projectDir . $image->getPath())) {
                    $success = false;
                }
            }
        }

        return $success;
    }

    protected function makeDelete() :bool
    {
        try {
            $this->manager->remove($this->functArgs->get('trick'));
            $this->manager->flush();
        } catch (\Exception $e) {
            $this->errMessages->add(self::ERR_DB_ACCESS);
            return false;
        }

        return true;
    }

    // ============================================================================================
    // OUT
    // ============================================================================================
    public function getHttpResponseCode(): int
    {
        return $this->responseHttpCode;
    }

    // ============================================================================================
    // CHECK PARAMETERS
    // ============================================================================================
    protected function checkParameters(): bool
    {
        if (null === $this->functArgs->get('user')) {
            $this->errMessages->add(self::ERR_USER_UNKNOWN);
            return false;
        }

        if (null === $this->functArgs->get('trick')) {
            $this->errMessages->add(self::ERR_TRICK_EMPTY);
            return false;
        }

        return true;
    }

    // ============================================================================================
    // TOOLS
    // ============================================================================================
    protected function initHelper(): void
    {
        parent::initHelper();

        $this->responseHttpCode = 200;
    }

}