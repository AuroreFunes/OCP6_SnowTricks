<?php

namespace App\Controller;

use App\Entity\Trick;
use App\Repository\TrickRepository;
use App\Service\Trick\LoadTricksService;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

use function Symfony\Component\DependencyInjection\Loader\Configurator\env;

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
        $service->loadTricks(4);

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
