<?php

namespace App\Service;

use Doctrine\Persistence\ManagerRegistry;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

abstract class SendMailServiceHelper extends ServiceHelper {

    protected Environment $twig;

    protected function __construct(ManagerRegistry $manager)
    {
        parent::__construct($manager);

        // we need twig, and templates are in "templates" directory
        $debug = ($_ENV['APP_ENV'] === "dev") ? true : false;
        $loader = new FilesystemLoader('../templates');
        $this->twig = new Environment($loader, ['debug' => $debug]);
    }

}