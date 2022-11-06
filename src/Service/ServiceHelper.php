<?php

namespace App\Service;

use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Persistence\ObjectManager;

abstract class ServiceHelper {

    protected ObjectManager $manager;

    // UTILITIES
    protected bool $status;
    protected ArrayCollection $functArgs;
    protected ArrayCollection $functResult;
    protected ArrayCollection $errMessages;

    // ERRORS
    protected const ERR_DB_ACCESS = "Une erreur interne s'est produite.";

    public function __construct(ManagerRegistry $manager) {
        $this->manager = $manager->getManager();

        $this->initHelper();
    }

    protected function initHelper(): void
    {
        $this->status   = false;
        $this->functArgs    = new ArrayCollection();
        $this->functResult  = new ArrayCollection();
        $this->errMessages  = new ArrayCollection();
    }

    public function getStatus()
    {
        return $this->status;
    }

    public function getArguments()
    {
        return $this->functArgs;
    }

    public function getResult()
    {
        return $this->functResult;
    }

    public function getErrorsMessages()
    {
        return $this->errMessages;
    }

}