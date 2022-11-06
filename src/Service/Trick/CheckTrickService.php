<?php

namespace App\Service\Trick;

use App\Entity\Trick;
use App\Repository\TrickRepository;
use App\Service\ServiceHelper;
use Doctrine\Persistence\ManagerRegistry;

class CheckTrickService extends ServiceHelper
{
    protected const ERR_TRICK_ID_EMPTY  = "Le numéro de la figure doit être défini.";
    protected const ERR_INVALID_TRICKS_ID   = "La demande n'est pas valide.";
    protected const ERR_TRICK_EMPTY     = "La figure n'est pas définie.";

    protected TrickRepository $trickRepository;

    public function __construct(ManagerRegistry $manager, TrickRepository $trickRepository)
    {
        parent::__construct($manager);

        $this->trickRepository  = $trickRepository;
    }


    // ============================================================================================
    // ENTRYPOINT
    // ============================================================================================
    public function checkTrick(array $params): self
    {
        $this->initHelper();

        if (false === $this->checkParameters($params)) {
            return $this;
        }

        if (false === $this->getTrick()) {
            return $this;
        }
        
        $this->status = true;
        return $this;
    }

    // ============================================================================================
    // JOBS
    // ============================================================================================
    protected function getTrick(): bool
    {
        if (null === $trick = $this->trickRepository->find($this->functArgs->get('id'))) {
            $this->errMessages->add(self::ERR_TRICK_EMPTY);
            return false;
        }

        $this->functResult->set('trick', $trick);

        return true;
    }


    // ============================================================================================
    // CHECK PARAMETERS
    // ============================================================================================
    protected function checkParameters(array $params): bool
    {
        if (empty($params['id'])) {
            $this->errMessages->add(self::ERR_TRICK_ID_EMPTY);
            return false;
        }

        if (false === filter_var($params['id'], FILTER_VALIDATE_INT, 
            ['options' => ['min_range' => 1]])) {
            $this->errMessages->add(self::ERR_INVALID_TRICKS_ID);
            return false;
        }

        $this->functArgs->set('id', $params['id']);

        return true;
    }

}