<?php

namespace App\Service\Trick;

use App\Entity\TrickVideo;
use App\Entity\User;
use App\Repository\TrickVideoRepository;
use App\Service\ServiceHelper;
use Doctrine\Persistence\ManagerRegistry;

class DeleteTrickVideoService extends ServiceHelper
{

    protected const ERR_USER_UNKNOWN    = "L'utilisateur doit être connecté.";
    protected const ERR_ID_EMPTY        = "Le numéro de l'image doit être défini.";
    protected const ERR_INVALID_ID      = "La demande n'est pas valide.";
    protected const ERR_VIDEO_EMPTY     = "L'image n'a pas été trouvée.";
    protected const ERR_DELETE_VIDEO    = "La vidéo n'a pas pu être supprimée.";

    protected string $projectDir;
    protected TrickVideoRepository $trickVideoRepository;
    protected int $responseHttpCode;


    public function __construct(ManagerRegistry $manager, TrickVideoRepository $trickVideoRepository)
    {
        parent::__construct($manager);

        $this->trickVideoRepository = $trickVideoRepository;
        $this->responseHttpCode     = 200;
    }

    // ============================================================================================
    // ENTRYPOINT
    // ============================================================================================
    public function deleteTrickVideo(?User $user, array $params) :self
    {
        $this->initHelper();

        // save parameters
        $this->functArgs->set('user', $user);

        if (false === $this->checkParameters($params)) {
            $this->responseHttpCode = 400;
            return $this;
        }

        if (false === $this->getTrickVideo()) {
            $this->responseHttpCode = 400;
            return $this;
        }

        if (false === $this->deleteVideo()) {
            $this->responseHttpCode = 500;
            return $this;
        }

        $this->status = true;
        return $this;
    }

    // ============================================================================================
    // JOBS
    // ============================================================================================
    protected function getTrickVideo(): bool
    {
        $trickVideo = $this->trickVideoRepository->find($this->functArgs->get('id'));

        if (null === $trickVideo) {
            $this->errMessages->add(self::ERR_VIDEO_EMPTY);
            return false;
        }

        $this->functArgs->set('video', $trickVideo);
        return true;
    }

    protected function deleteVideo(): bool
    {
        try {
            $this->manager->remove($this->functArgs->get('video'));
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
    protected function checkParameters(array $params): bool
    {
        if (null === $this->functArgs->get('user')) {
            $this->errMessages->add(self::ERR_USER_UNKNOWN);
            return false;
        }

        if (empty($params['id'])) {
            $this->errMessages->add(self::ERR_ID_EMPTY);
            return false;
        }

        if (false === filter_var($params['id'], FILTER_VALIDATE_INT, 
            ['options' => ['min_range' => 1]])) {
            $this->errMessages->add(self::ERR_INVALID_ID);
            return false;
        }

        $this->functArgs->set('id', $params['id']);

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