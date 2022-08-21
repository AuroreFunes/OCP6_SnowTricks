<?php

namespace App\Controller;

use App\Entity\Trick;
use App\Form\TrickType;
use App\Service\Trick\LoadTricksService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TrickController extends AbstractController
{

    const TRICKS_PER_PAGE = 4;

    /**
     * @Route("/showTrick/{id}", name="app_trick_show")
     */
    public function showTrick(Trick $trick = null)
    {
        $twigParams = [];

        if (null === $trick) {
            $twigParams['title'] = "Erreur";
            $twigParams['pageTitle'] = "Erreur";
            $twigParams['pageSubTitle'] = "La demande n'a pas pu aboutir";
            $twigParams['messages'] = ["La figure demandée n'existe pas ou n'a pas été trouvée."];
            $twigParams['back'] = "";

            return $this->render('pages/genericMessagePage.html.twig', $twigParams);
        }

        $twigParams['pageTitle'] = "";
        $twigParams['pageSubTitle'] = "";
        $twigParams['trick'] = $trick;

        return $this->render('pages/tricks/showTrick.html.twig', $twigParams);
    }

    /**
     * @Route("/loadMoreTricks/", name="app_trick_load_more")
     */
    public function loadMoreTricks(Request $request, LoadTricksService $service)
    {
        $params = $request->request->all();

        $service->loadTricks(self::TRICKS_PER_PAGE, $params);

        if (false === $service->getStatus()) {
            return new JsonResponse("Aucune donnée n'a pu être obtenue.", 
                $service->getResult()['httpCode']);
        }

        // create response
        $data['html'] = $this->render(
                'pages/tricks/trickElement.html.twig', 
                ['tricks' => $service->getResult()['tricks']]
            )->getContent();
        $data['moreTricks'] = self::TRICKS_PER_PAGE > $service->getResult()['tricksNb'] ? false : true;

        $response = new Response();
        $response->headers->set('Content-Type', 'application/json');
        $response->setContent(json_encode($data));

        return $response;
    }

}