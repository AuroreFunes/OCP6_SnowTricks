<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Trick;
use App\Entity\TrickComment;
use App\Form\TrickType;
use App\Form\EditTrickType;
use App\Form\TrickCommentType;
use App\Service\Trick\AddTrickCommentService;
use App\Service\Trick\EditTrickService;
use App\Service\Trick\LoadTricksService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

use function Symfony\Component\DependencyInjection\Loader\Configurator\env;

class TrickController extends AbstractController
{

    /**
     * @Route("/showTrick/{id}", name="app_trick_show")
     */
    public function showTrick(Trick $trick = null, Request $request, AddTrickCommentService $service)
    {
        $twigParams = [];

        if (null === $trick) {
            $this->addFlash('error', "La figure à laquelle vous essayez d'accéder n'existe pas ou a été supprimée.");
            return $this->redirectToRoute('app_home');
        }

        $twigParams['trick'] = $trick;
        
        $comment = new TrickComment();
        $form = $this->createForm(TrickCommentType::class, $comment);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var ?User $user */
            if (null === $user = $this->getUser()) {
                $this->addFlash('error', "Vous devez être connecté pour ajouter un commentaire !");
                return $this->redirectToRoute('app_trick_show', ['id' => $trick->getId()]);
            }

            // add the new comment
            $service->addComment($comment, $trick, $user);

            if (false === $service->getStatus()) {
                $this->addFlash('error', "Une erreur interne s'est produite. Merci de réessayer plus tard.");
                return $this->redirectToRoute('app_trick_show', ['id' => $trick->getId()]);
            }

            $this->addFlash('success', "Votre commentaire a bien été ajouté.");
            return $this->redirectToRoute('app_trick_show', ['id' => $trick->getId()]);
        }

        $twigParams['commentForm'] = $form->createView();
        return $this->render('pages/tricks/showTrick.html.twig', $twigParams);
    }

    /**
     * @Route("/loadMoreTricks/", name="app_trick_load_more")
     */
    public function loadMoreTricks(Request $request, LoadTricksService $service)
    {
        $params = $request->request->all();

        $service->loadTricks($_ENV['TRICKS_PER_PAGE'], $params);

        if (false === $service->getStatus()) {
            return new JsonResponse("Aucune donnée n'a pu être obtenue.", 
                $service->getResult()['httpCode']);
        }

        // create response
        $data['html'] = $this->render(
                'pages/tricks/trickElement.html.twig', 
                ['tricks' => $service->getResult()['tricks']]
            )->getContent();
        $data['moreTricks'] = $_ENV['TRICKS_PER_PAGE'] > $service->getResult()['tricksNb'] ? false : true;

        $response = new Response();
        $response->headers->set('Content-Type', 'application/json');
        $response->setContent(json_encode($data));

        return $response;
    }


    /**
     * @Route("/createTrick/", name="app_trick_create")
     */
    public function createTrick(Request $request, EditTrickService $service)
    {
        if (null === $user = $this->getUser()) {
            $this->addFlash('error', "Vous devez être connecté pour utiliser cette fonctionnalité.");
            return $this->redirectToRoute('app_home');
        }

        $trick = new Trick();

        $form = $this->createForm(TrickType::class, $trick);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $service->editTrick(
                $trick, 
                $user, 
                $form['pictures']->getData(), 
                $form['defaultPicture']->getData()
            );

            if (true === $service->getStatus()) {
                $this->addFlash('success', "La nouvelle figure a bien été créée.");
                return $this->redirectToRoute('app_trick_show', ['id' => $service->getResult()['trickId']]);
            }

            $this->addFlash('error', "Une ou plusieurs erreurs se sont produites.");

            if (!empty($service->getResult()['trickId'])) {
                $this->addFlash('error', "Toutes les images n'ont pas pu être ajoutées.");
                return $this->redirectToRoute('app_trick_show', ['id' => $service->getResult()['trickId']]);
            }

            $this->addFlash('error', "La figure n'a pas pu être créée.");
            return $this->redirectToRoute('app_home');
        }

        $twigParams['trickForm'] = $form->createView();
        $twigParams['title'] = "Créer une figure";
        $twigParams['formTitle'] = "Créer une nouvelle figure";
        $twigParams['pageTitle'] = "Créer une nouvelle figure";
        $twigParams['pageSubTitle'] = "";
        
        return $this->render('pages/tricks/trickForm.html.twig', $twigParams);
    }


    /**
     * @Route("/editTrick/{id}", name="app_trick_edit")
     */
    public function editTrick(?Trick $trick, Request $request, EditTrickService $service)
    {
        if (null === $user = $this->getUser()) {
            $this->addFlash('error', "Vous devez être connecté pour utiliser cette fonctionnalité.");
            return $this->redirectToRoute('app_home');
        }

        if (null === $trick) {
            $this->addFlash('error', "La figure n'a pas été trouvée.");
            return $this->redirectToRoute('app_home');
        }

        $form = $this->createForm(EditTrickType::class, $trick);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $service->editTrick(
                $trick, 
                $user, 
                $form['pictures']->getData(), 
                $form['defaultPicture']->getData()
            );

            if (true === $service->getStatus()) {
                $this->addFlash('success', "La mise à jour a été effectuée.");
                return $this->redirectToRoute('app_trick_show', ['id' => $service->getResult()['trickId']]);
            }

            $this->addFlash('error', "Une ou plusieurs erreurs se sont produites.
                Il est possible que toutes les images n'aient pas pu être ajoutées.");
        }

        $twigParams['trickForm'] = $form->createView();
        $twigParams['trick'] = $trick;
        $twigParams['title'] = "Modifier la figure";
        $twigParams['formTitle'] = "Modifier la figure";
        $twigParams['pageTitle'] = "Modifier la figure : ";
        $twigParams['pageSubTitle'] =  $trick->getName();
        
        return $this->render('pages/tricks/trickForm.html.twig', $twigParams);
    }

    /**
     * @Route("/deleteTrick/{id}", name="app_trick_delete")
     */
    public function deleteTrick(?Trick $trick)
    {
        if (null === $user = $this->getUser()) {
            $this->addFlash('error', "Vous devez être connecté pour utiliser cette fonctionnalité.");
            return $this->redirectToRoute('app_home');
        }

        if (null === $trick) {
            $this->addFlash('error', "La figure n'a pas été trouvée.");
            return $this->redirectToRoute('app_home');
        }

        // supprimer les images (fichiers) !!!

        // à supprimer en bdd : vidéos, images, historique, commentaires, puis figure
    }

    /**
     * @Route("/commentTrick/{id}", name="app_trick_addComment")
     */
    public function addTrickComment(Trick $trick)
    {
        /** @var User $user */
        if (null === $user = $this->getUser()) {
            $this->addFlash('error', "Vous devez être connecté pour ajouter un commentaire !");
            return $this->redirectToRoute('app_user_login');
        }

        if (null === $trick) {
            $this->addFlash('error', "La figure n'a pas été trouvée.");
            return $this->redirectToRoute('app_home');
        }




    }


}