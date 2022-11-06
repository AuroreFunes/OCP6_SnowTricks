<?php

namespace App\Controller;

use App\Service\Trick\LoadTricksService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{

    /**
     * @Route("/", name="app_home")
     */
    public function index(Request $request, LoadTricksService $service): Response
    {
        $params = [];
        $params['session'] = $request->request->all();

        //$service->loadTricks(getenv('TRICKS_PER_PAGE'));
        $service->loadTricks($_ENV['TRICKS_PER_PAGE']);

        if (false === $service->getStatus()) {
            $twigParams['pageTitle'] = "Oups !";
            $twigParams['pageSubTitle'] = "Il n'y a pas de contenu";
            $twigParams['title'] = "Revenez plus tard !";
            $twigParams['messages'] = ["Aucune figure n'a été créée pour le moment."];
            $twigParams['back'] = "";

            return $this->render('pages/genericMessagePage.html.twig', $twigParams);
        }

        return $this->render('pages/index.html.twig', [
            'pageTitle' => "SnowTricks",
            'pageSubTitle' => "Apprenez et partagez sur les figures de Snow !",
            'tricks' => $service->getResult()['tricks']
        ]);
    }
}
