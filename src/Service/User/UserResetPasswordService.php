<?php

namespace App\Service\User;

use App\Entity\User;
use App\Service\SendMailServiceHelper;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;


class UserResetPasswordService extends SendMailServiceHelper
{

    protected const ERR_INVALID_USER        = "L'utilisateur est invalide.";
    protected const ERR_INACTIVE_USER       = "L'utilisateur n'est pas autorisé à faire cette demande.";
    protected const ERR_INVALID_REQUEST     = "La demande est invalide.";
    protected const ERR_EXPIRED_TOKEN       = "La demande a expiré.";
    protected const ERR_INVALID_PASSWORD    = "Le nouveau mot de passe doit être renseigné 
        et contenir au moins 8 caractères, dont 1 minuscule, 1 majuscule et 1 chiffre.";
    protected const ERR_PWD_NOT_IDENTICAL   = "Les mots de passe doivent être identiques.";

    protected UserPasswordHasherInterface   $passwordHasher;

    public function __construct(
        ManagerRegistry $manager,
        UserPasswordHasherInterface $passwordHasher
    ) {
        parent::__construct($manager);

        $this->passwordHasher = $passwordHasher;
    }


    // ============================================================================================
    // ENTRYPOINT
    // ============================================================================================
    public function resetPassword(?User $user, string $token, array $datas): self
    {
        $this->initHelper();
        $this->functArgs->set('user', $user);
        $this->functArgs->set('token', $token);

        if (false === $this->checkParameters($datas)) {
            return $this;
        }

        if (false === $this->changePassword()) {
            return $this;
        }

        $this->status = true;
        return $this;
    }


    // ============================================================================================
    // JOBS
    // ============================================================================================
    protected function changePassword()
    {
        // change password
        $this->functArgs->get('user')
            ->setPassword($this->passwordHasher->hashPassword(
                $this->functArgs->get('user'), $this->functArgs->get('password')
            ));
        
        try {
            // remove token
            $this->manager->remove($this->functArgs->get('user')->getUserToken());

            $this->manager->persist($this->functArgs->get('user'));
            $this->manager->flush();
        } catch (\Exception $e) {
            $this->errMessages->add(self::ERR_INTERNAL_ERROR);
            return false;
        }
        
        return true;
    }


    // ============================================================================================
    // CHECK PARAMETERS
    // ============================================================================================
    protected function checkParameters(array $datas): bool
    {
        // check user
        if (null === $this->functArgs->get('user')) {
            $this->errMessages->add(self::ERR_INVALID_USER);
            return false;
        }

        // user is active ?
        if (false ===  $this->functArgs->get('user')->getIsActive()) {
            $this->errMessages->add(self::ERR_INACTIVE_USER);
            return false;
        }

        // check token
        if ($this->functArgs->get('token') !== 
                $this->functArgs->get('user')->getUserToken()->getToken()) {
            $this->errMessages->add(self::ERR_INVALID_REQUEST);
            return false;
        }

        // check token date
        $date = new \DateTime();
        $date->sub(new \DateInterval('P1D'));
        if ($date > $this->functArgs->get('user')->getUserToken()->getCreated()) {
            $this->errMessages->add(self::ERR_EXPIRED_TOKEN);
            return false;
        }

        // check password
        if (empty($datas['password'])) {
            $this->errMessages->add(self::ERR_INVALID_PASSWORD);
            return false;
        }

        // (?=\S{8,})   : 8 characters or more
        // (?=\S*[a-z]) : at least one lowercase letter
        // (?=\S*[A-Z]) : at least one upper case letter
        // (?=\S*[\d])  : at least one number
        if (1 !== preg_match('~(?=\S{8,})(?=\S*[a-z])(?=\S*[A-Z])(?=\S*[\d])~', $datas['password'])) {
            $this->errMessages->add(self::ERR_INVALID_PASSWORD);
            return false;
        }

        if ($datas['password'] !== $datas['password_confirm']) {
            $this->errMessages->add(self::ERR_PWD_NOT_IDENTICAL);
            return false;
        }

        $this->functArgs->set('password', $datas['password']);

        return true;
    }

}