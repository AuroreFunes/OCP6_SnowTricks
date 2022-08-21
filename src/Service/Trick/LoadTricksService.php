<?php

namespace App\Service\Trick;

use App\Service\ServiceHelper;
use App\Repository\TrickRepository;

class LoadTricksService extends ServiceHelper
{
    // ERRORS MESSAGES
    const ERR_INVALID_TRICKS_NUMBER = "Le nombre de figures à charger n'est pas valide.";
    const ERR_INVALID_PAGE_NB       = "Le numéro de page n'est pas valide.";
    const ERR_ANY_TRICK             = "Il n'y a pas de figure supplémentaire à charger.";

    // PRIVATE USE
    private TrickRepository $trickRepository;

    public function __construct(TrickRepository $trickRepository)
    {
        parent::__construct();

        $this->trickRepository = $trickRepository;
    }

    // ========================================================================================
    // ENTRYPOINT
    // ========================================================================================
    public function loadTricks(int $numberToLoad, array $params = []) :self
    {
        // save parameters
        $this->functArgs->set('numberToLoad', $numberToLoad);

        if (false === $this->checkParameters($params)) {
            return $this;
        }

        if (false === $this->getTricks()) {
            return $this;
        }

        $this->status = true;
        return $this;
    }

    // ========================================================================================
    // JOBS
    // ========================================================================================
    private function getTricks()
    {
        try {
            $tricks = $this->trickRepository->findBy(
                [],
                null,
                $this->functArgs->get('numberToLoad'), 
                $this->functArgs->get('offset')
            );
        } catch (\Exception $e) {
            $this->functResult->set('httpCode', 500);
            $this->errMessages->add(SELF::ERR_DB_ACCESS . $e->getMessage());
            return false;
        }

        if (empty($tricks)) {
            $this->functResult->set('httpCode', 204);
            $this->errMessages->add(SELF::ERR_ANY_TRICK);
            return false;
        }
        
        $this->functResult->set('httpCode', 200);
        $this->functResult->set('tricks', $tricks);
        $this->functResult->set('tricksNb', count($tricks));

        return true;
    }

    // ========================================================================================
    // CHECK PARAMETERS
    // ========================================================================================
    private function checkParameters(array $params)
    {
        if (false === filter_var($this->functArgs->get('numberToLoad'), FILTER_VALIDATE_INT, 
            ['options' => ['min_range' => 1]])) {
            $this->errMessages->add(SELF::ERR_INVALID_TRICKS_NUMBER);
            return false;
        }

        if (empty($params['page'])) {
            $this->functArgs->set('offset', 0);
            return true;
        }

        if (false === filter_var($params['page'], FILTER_VALIDATE_INT, 
            ['options' => ['min_range' => 1]])) {
            $this->errMessages->add(SELF::ERR_INVALID_PAGE_NB);
            return false;
        }

        $this->functArgs->set('offset', $params['page'] * $this->functArgs->get('numberToLoad'));

        return true;
    }

}