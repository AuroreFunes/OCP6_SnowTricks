<?php

namespace App\Service\User;

use App\Entity\User;
use App\Service\ServiceHelper;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class UpdateUserPictureService extends ServiceHelper
{

    private const ALLOWED_EXTENSIONS    = ['jpg', 'jpeg', 'png', 'bmp'];
    private const MAX_SIZE_ALLOWED      = 500;       // unit = ko !
    private const ERR_INVALID_FILE_TYPE = "Le type du fichier n'est pas valide.";
    private const ERR_FILE_TOO_LARGE    = "Le fichier dépasse le poids autorisé.";
    private const ERR_INVALID_FILE_EXTENSION    = "Ce format de fichier n'est pas pris en charge.";

    private string $projectDir;

    public function __construct(
        ManagerRegistry $manager,
        string $projectDir
    ) {
        parent::__construct($manager);
        
        $this->projectDir = $projectDir;   
    }

    // ========================================================================================
    // ENTRYPOINT
    // ========================================================================================
    public function updateUserPicture(User $user, UploadedFile $picture): self
    {
        $this->functArgs->set('user', $user);
        $this->functArgs->set('picture', $picture);

        if (false === $this->checkFile()) {
            // we need remove temp file ?
            return $this;
        }

        $this->savePicture();

        $this->status = true;
        return $this;
    }


    public function savePicture()
    {
        $destination = $this->projectDir . '\public\img\users';

        $newFilename = $this->functArgs->get('user')->getId() 
            . "_" . "profilPicture." 
            . $this->functArgs->get('picture')->getClientOriginalExtension();

        $this->functArgs->get('picture')->move($destination, $newFilename);
        $this->functArgs->get('user')->setProfilePicture($newFilename);

        $this->manager->persist($this->functArgs->get('user'));
        $this->manager->flush();

        return true;
    }

    // ========================================================================================
    // CHECK PARAMETERS
    // ========================================================================================
    protected function checkFile(): bool
    {
        // check type
        if ("image" !== substr($this->functArgs->get('picture')->getClientMimeType(), 0, 5)) {
            $this->errMessages->add(self::ERR_INVALID_FILE_TYPE);
            return false;
        }

        // check size
        if (1024 * self::MAX_SIZE_ALLOWED < $this->functArgs->get('picture')->getSize()) {
            $this->errMessages->add(self::ERR_FILE_TOO_LARGE);
            return false;
        }

        // check extension
        if (!in_array(strtolower($this->functArgs->get('picture')->getClientOriginalExtension()), 
            self::ALLOWED_EXTENSIONS)) {
            $this->errMessages->add(self::ERR_INVALID_FILE_EXTENSION);
            return false;
        }

        return true;
    }

}