<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Trick;
use App\Entity\TrickComment;
use App\Form\TrickType;
use App\Form\EditTrickType;
use App\Form\TrickCommentType;
use App\Service\Trick\AddTrickCommentService;
use App\Service\Trick\CheckTrickService;
use App\Service\Trick\DeleteTrickImageService;
use App\Service\Trick\DeleteTrickService;
use App\Service\Trick\DeleteTrickVideoService;
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
            $this->addFlash('error', 
                "La figure à laquelle vous essayez d'accéder n'existe pas ou a été supprimée.");
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
                return $this->redirectToRoute('app_trick_show', [
                    'id' => $trick->getId() . "-" . $trick->getSlug()
                ]);
            }

            // add the new comment
            $service->addComment($comment, $trick, $user);

            if (false === $service->getStatus()) {
                $this->addFlash('error', "Une erreur interne s'est produite. Merci de réessayer plus tard.");
                return $this->redirectToRoute('app_trick_show', [
                    'id' => $trick->getId() . "-" . $trick->getSlug()
                ]);
            }

            $this->addFlash('success', "Votre commentaire a bien été ajouté.");
            return $this->redirectToRoute('app_trick_show', [
                'id' => $trick->getId() . "-" . $trick->getSlug()
            ]);
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
                $form['defaultPicture']->getData(),
                $form['videos']->getData()
            );

            if (true === $service->getStatus()) {
                $this->addFlash('success', "La nouvelle figure a bien été créée.");
                return $this->redirectToRoute('app_trick_show', [
                    'id' => $service->getResult()['trickId'] . "-" .
                    $service->getArguments()['trick']->getSlug()
                ]);
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
                $form['defaultPicture']->getData(),
                $form['videos']->getData()
            );

            if (true === $service->getStatus()) {
                $this->addFlash('success', "La mise à jour a été effectuée.");
                return $this->redirectToRoute('app_trick_show', [
                    'id' => $trick->getId() . "-" . $trick->getSlug()
                ]);
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
    public function deleteTrick(?Trick $trick, DeleteTrickService $service)
    {
        if (null === $trick) {
            $this->addFlash('error', "La figure n'existe pas.");
            return $this->redirectToRoute('app_home');
        }

        if (null === $user = $this->getUser()) {
            $this->addFlash('error', "Vous devez être connecté pour utiliser cette fonctionnalité.");
            return $this->redirectToRoute('app_trick_show', [
                'id' => $trick->getId() . "-" . $trick->getSlug()
            ]);
        }

        $service->deleteTrick($user, $trick);

        if (false === $service->getStatus()) {
            foreach($service->getResult() as $message) {
                $this->addFlash('warning', $message);
            }

            return $this->redirectToRoute('app_home');
        }

        $this->addFlash('success', "La suppression a bien été effectuée.");
        return $this->redirectToRoute('app_home');
    }

    // ============================================================================================
    // XML HTTP REQUEST
    // ============================================================================================

    /**
     * @Route("/deleteTrick/", name="xhr_trick_delete")
     */
    public function xhrDeleteTrick(
        Request $request, 
        CheckTrickService $checkTrickService, 
        DeleteTrickService $deleteService
    )
    {
        if (null === $user = $this->getUser()) {
            $this->addFlash('error', "Vous devez être connecté pour utiliser cette fonctionnalité.");
            return new JsonResponse("Accès impossible.", 400);
        }

        $params = $request->request->all();

        $checkTrickService->checkTrick($params);

        if (false === $checkTrickService->getStatus()) {
            $this->addFlash('error', "La figure n'a pas été trouvée ou n'existe pas.");
            return new JsonResponse("La figure n'existe pas.", 400);
        }

        $deleteService->deleteTrick($user, $checkTrickService->getResult()['trick']);

        if (false === $deleteService->getStatus()) {
            foreach($deleteService->getResult() as $message) {
                $this->addFlash('warning', $message);
            }

            return new JsonResponse($deleteService->getResult(), $deleteService->getHttpResponseCode());
        }

        $this->addFlash('success', "La suppression a bien été effectuée.");
        return new JsonResponse("La suppression a bien été effectuée.", $deleteService->getHttpResponseCode());
    }

    /**
     * @Route("/deleteImage/", name="xhr_trick_delete_image")
     */
    public function xhrDeleteTrickImage(Request $request, DeleteTrickImageService $service)
    {
        if (null === $user = $this->getUser()) {
            $this->addFlash('error', "Vous devez être connecté pour utiliser cette fonctionnalité.");
            return new JsonResponse("Accès impossible.", 400);
        }

        $params = $request->request->all();

        $service->deleteTrickImage($user, $params);

        if (false === $service->getStatus()) {
            foreach($service->getResult() as $message) {
                $this->addFlash('error', $message);
            }

            return new JsonResponse($service->getResult(), $service->getHttpResponseCode());
        }

        $this->addFlash('success', "L'image a bien été supprimée.");
        return new JsonResponse("L'image a bien été supprimée.", 200);
    }

    /**
     * @Route("/deleteVideo/", name="xhr_trick_delete_video")
     */
    public function xhrDeleteTrickVideo(Request $request, DeleteTrickVideoService $service)
    {
        if (null === $user = $this->getUser()) {
            $this->addFlash('error', "Vous devez être connecté pour utiliser cette fonctionnalité.");
            return new JsonResponse("Accès impossible.", 400);
        }

        $params = $request->request->all();

        $service->deleteTrickVideo($user, $params);

        if (false === $service->getStatus()) {
            foreach($service->getResult() as $message) {
                $this->addFlash('error', $message);
            }

            return new JsonResponse($service->getResult(), $service->getHttpResponseCode());
        }

        $this->addFlash('success', "La vidéo a bien été supprimée.");
        return new JsonResponse("La vidéo a bien été supprimée.", 200);
    }

}