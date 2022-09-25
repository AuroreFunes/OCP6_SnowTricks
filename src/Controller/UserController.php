<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationType;
use App\Form\UserForgottenPasswordType;
use App\Service\User\SendActivationMailService;
use App\Service\User\UpdateUserPictureService;
use App\Service\User\UserAskNewPasswordService;
use App\Service\User\UserConfirmRegistrationService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

use App\Service\User\UserRegistrationService;


class UserController extends AbstractController
{

    /**
     * @Route("/login", name="app_user_login")
     */
    public function login(AuthenticationUtils $authenticationUtils) : Response
    {
        if (null !== $this->getUser()) {
            $this->addFlash('error', "Vous êtes déjà connecté !");
            return $this->redirectToRoute('app_home');
        }

        if (!empty($authenticationUtils->getLastAuthenticationError())) {
            $twigParams['error'] = "Les informations saisies ne sont pas valides.";
        }
        
        $twigParams['lastUsername'] = $authenticationUtils->getLastUsername();

        return $this->render('users/login.html.twig', $twigParams);
    }


    /**
     * @Route("/logout", name="app_user_logout")
     */
    public function logout()
    {

    }


    /**
     * @Route("/registration", name="app_user_registration")
     */
    public function registration(
        Request $request, 
        UserRegistrationService $registrationService,
        UpdateUserPictureService $userPictureService,
        SendActivationMailService $activationMailerService
    )
    {
        if (null !== $this->getUser()) {
            $this->addFlash('error', "Vous avez déjà un compte !");
            return $this->redirectToRoute('app_home');
        }
        
        $user = new User();

        $form = $this->createForm(RegistrationType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            
            // create user and token
            $registrationService->createUser($user);

            if (false === $registrationService->getStatus()) {
                // error : add flash message
                $this->addFlash('error', "Une erreur est survenue pendant la création de votre compte. 
                    Merci de réessayer dans quelques instants.");

                $twigParams['registrationForm'] = $form->createView();
                return $this->render('users/registration.html.twig', $twigParams);
            }

            // success : add flash message
            $this->addFlash('success', "Votre compte a bien été créé. 
                Vous recevrez d'ici quelques instants un courriel vous permettant de l'activer et de vous connecter.");

            // send activation mail (with message handler !)
            $activationMailerService->sendMail($registrationService->getResult()->get('user'));

            // profil picture
            /** @var ?UploadedFile $uploadedFile */
            if (null !== $uploadedFile = $form['profilePicture']->getData()) {

                $userPictureService->updateUserPicture($registrationService->getResult()->get('user'), $uploadedFile);

                if (false === $userPictureService->getStatus()) {
                    $this->addFlash('warning', 'Votre image de profil n\'a pas pu être enregistrée.
                        Vous pourrez en ajouter une ultérieurement.');
                }
            }

            return $this->redirectToRoute('app_home');
        }

        $twigParams['registrationForm'] = $form->createView();
        return $this->render('users/registration.html.twig', $twigParams);
    }


    /**
     * @Route("/confirmRegistration/{id}/{token}", name="app_user_confirmRegistration")
     */
    public function confirmRegistration(?User $user, string $token, UserConfirmRegistrationService $service)
    {
        // ckeck datas and make activation
        $service->activateUser($user, $token);

        if (false === $service->getStatus()) {
            $this->addFlash('error', "Une erreur s'est produite et votre compte n'a pas pu être activé.");
            return $this->redirectToRoute('app_home');
        }

        $this->addFlash('success', "Félicitations, votre compte a été activé ! 
            Vous pouvez maintenant vous connecter.");
        return $this->redirectToRoute('app_user_login');
    }

    /**
     * @Route("/forgottenPassword", name="app_user_forgottenPassword")
     */
    public function askNewPassword(Request $request, UserAskNewPasswordService $service)
    {

        $form = $this->createForm(UserForgottenPasswordType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $service->sendToken($form->getData()['email']);

            if (false === $service->getStatus()) {
                $this->addFlash('error', "Une erreur s'est produite.
                    Merci de réessayer dans quelques instants.");
                return $this->redirectToRoute('app_home');
            }

            $this->addFlash('success', "Votre demande a bien été prise en compte. 
                Si elle est valide, vous recevrez un courriel rapidement.");
            return $this->redirectToRoute('app_home');
        }

        $twigParams['forgottenPasswordForm'] = $form->createView();
        return $this->render('users/forgottenPassword.html.twig', $twigParams);
    }

}