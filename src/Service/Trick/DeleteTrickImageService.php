<?php

namespace App\Service\Trick;

use App\Entity\User;
use App\Repository\TrickImageRepository;
use App\Service\ServiceHelper;
use Doctrine\Persistence\ManagerRegistry;

class DeleteTrickImageService extends ServiceHelper
{

    protected const ERR_USER_UNKNOWN    = "L'utilisateur doit être connecté.";
    protected const ERR_ID_EMPTY        = "Le numéro de l'image doit être défini.";
    protected const ERR_INVALID_ID      = "La demande n'est pas valide.";
    protected const ERR_IMAGE_EMPTY     = "L'image n'a pas été trouvée.";
    protected const ERR_IMAGE_FILE_EMPTY    = "Le fichier de l'image n'a pas été trouvé.";
    protected const ERR_DELETE_IMAGE    = "Le fichier de l'image n'a pas pu être supprimé.";

    protected string $projectDir;
    protected TrickImageRepository $trickImageRepository;
    protected int $responseHttpCode;


    public function __construct(
        ManagerRegistry $manager, 
        TrickImageRepository $trickImageRepository, 
        string $projectDir
    ) {
        parent::__construct($manager, $projectDir);

        $this->trickImageRepository = $trickImageRepository;
        $this->projectDir           = $projectDir . '\public\img\tricks\\';
        $this->responseHttpCode     = 200;
    }

    // ============================================================================================
    // ENTRYPOINT
    // ============================================================================================
    public function deleteTrickImage(?User $user, array $params) :self
    {
        $this->initHelper();

        // save parameters
        $this->functArgs->set('user', $user);

        if (false === $this->checkParameters($params)) {
            $this->responseHttpCode = 400;
            return $this;
        }

        if (false === $this->getTrickImage()) {
            $this->responseHttpCode = 400;
            return $this;
        }

        if (false === $this->removeImageFile()) {
            $this->responseHttpCode = 500;
            return $this;
        }

        if (false === $this->deleteImage()) {
            $this->responseHttpCode = 500;
            return $this;
        }

        $this->status = true;
        return $this;
    }

    // ============================================================================================
    // JOBS
    // ============================================================================================
    protected function getTrickImage(): bool
    {
        $trickImage = $this->trickImageRepository->find($this->functArgs->get('id'));

        if (null === $trickImage) {
            $this->errMessages->add(self::ERR_IMAGE_EMPTY);
            return false;
        }

        $this->functArgs->set('image', $trickImage);
        return true;
    }

    protected function removeImageFile(): bool
    {
        if (!file_exists($this->projectDir . $this->functArgs->get('image')->getPath())) {
            $this->errMessages->add(self::ERR_IMAGE_FILE_EMPTY);
            return false;
        }

        if (false === unlink($this->projectDir . $this->functArgs->get('image')->getPath())) {
            $this->errMessages->add(self::ERR_DELETE_IMAGE);
            return false;
        }
        
        return true;
    }

    protected function deleteImage(): bool
    {
        try {
            $this->manager->remove($this->functArgs->get('image'));
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