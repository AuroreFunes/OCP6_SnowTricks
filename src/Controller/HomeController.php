<?php

namespace App\Controller;

use App\Entity\Trick;
use App\Repository\TrickRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    /**
     * @Route("/", name="app_home")
     */
    public function index(Request $request, TrickRepository $trickRepo): Response
    {
        $params = [];
        $params['session'] = $request->request->all();

        $tricks = $trickRepo->findAll();


        return $this->render('pages/index.html.twig', [
            'pageTitle' => "SnowTricks",
            'pageSubTitle' => "Apprenez et partagez sur les figures de Snow !",
            'tricks' => $tricks
        ]);
    }
}
