<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationType;
use Doctrine\Persistence\ObjectManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Serializer\Normalizer\DataUriNormalizer;
use function Symfony\Component\DependencyInjection\Loader\Configurator\env;

use App\Service\User\UserRegistrationService;

class UserController extends AbstractController
{
    /**
     * @Route("/registration", name="app_user_registration")
     */
    public function registration(
        Request $request, 
        //ObjectManager $manager, 
        UserPasswordHasherInterface $passwordHasher,
        UserRegistrationService $registrationService
    )
    {
        $user = new User();

        $form = $this->createForm(RegistrationType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $datas = $request->request->all()['registration'];

            //dd($form['imageFile']->getData())
            // profilePicture

            if (null === $user = $registrationService->createUser($user)) {

            }
            

            
            // profil picture
            /** @var ?UploadedFile $uploadedFile */
            if (null !== $uploadedFile = $form['profilePicture']->getData()) {
                // we can also find the file here => $request->files->get('registration')['profilePicture']
                $destination = $this->getParameter('kernel.project_dir').'\public\img\users';

                // filename without extension => pathinfo($uploadedFile->getClientOriginalName(), PATHINFO_FILENAME)
                $originalFilename = $uploadedFile->getClientOriginalName();
                $newFilename = $originalFilename;   // TODO secure the name and ensure that it is unique

                $uploadedFile->move(
                    $destination,
                    $newFilename
                );

                $user->setProfilePicture($newFilename);
            }

//123Test!
            //$manager->persist($user);
            //$manager->flush();
dd($user);
            $this->redirectToRoute('app_user_login');
        }

        $twigParams['registrationForm'] = $form->createView();

        return $this->render('users/registration.html.twig', $twigParams);
    }

    /**
     * @Route("/login", name="app_user_login")
     */
    public function login(AuthenticationUtils $authenticationUtils) : Response
    {

        //$twigParams['error'] = $authenticationUtils->getLastAuthenticationError();
        $twigParams['error'] = "Les informations saisies ne sont pas valides.";
        $twigParams['lastUsername'] = $authenticationUtils->getLastUsername();

        return $this->render('users/login.html.twig', $twigParams);
    }

    /**
     * @Route("/logout", name="app_user_logout")
     */
    public function logout()
    {

    }

}