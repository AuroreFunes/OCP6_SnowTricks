<?php

namespace App\Service;

use App\Service\ServiceHelper;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class PictureServiceHelper extends ServiceHelper
{
    protected const ALLOWED_EXTENSIONS    = ['jpg', 'jpeg', 'png', 'bmp'];
    protected const MAX_SIZE_ALLOWED      = 500;       // unit = ko !
    protected const ERR_INVALID_FILE_TYPE = "Le type du fichier n'est pas valide.";
    protected const ERR_FILE_TOO_LARGE    = "Le fichier dépasse le poids autorisé.";
    protected const ERR_INVALID_FILE_EXTENSION  = "Ce format de fichier n'est pas pris en charge.";
    protected const ERR_UNABLE_TO_SAVE_PICTURE  = "L'image n'a pas pu être sauvegardée.";

    protected string $projectDir;


    public function __construct(ManagerRegistry $manager, string $projectDir)
    {
        parent::__construct($manager);

        $this->projectDir = $projectDir;
    }

    // ============================================================================================
    // CHECK PARAMETERS
    // ============================================================================================
    protected function checkPicture(UploadedFile $picture): bool
    {
        // check type
        if ("image" !== substr($picture->getClientMimeType(), 0, 5)) {
            $this->errMessages->add(self::ERR_INVALID_FILE_TYPE);
            return false;
        }

        // check size
        if (1024 * self::MAX_SIZE_ALLOWED < $picture->getSize()) {
            $this->errMessages->add(self::ERR_FILE_TOO_LARGE);
            return false;
        }

        // check extension
        if (!in_array(strtolower($picture->getClientOriginalExtension()), 
            self::ALLOWED_EXTENSIONS)) {
            $this->errMessages->add(self::ERR_INVALID_FILE_EXTENSION);
            return false;
        }

        return true;
    }

    // ============================================================================================
    // JOBS
    // ============================================================================================
    public function savePicture(UploadedFile $picture, string $destination, string $nameWithoutExtension)
    {
        $newFilename = $nameWithoutExtension . "." . $picture->getClientOriginalExtension();

        try {
            $picture->move($destination, $newFilename);
        } catch (\Exception $e) {
            $this->errMessages->add(self::ERR_UNABLE_TO_SAVE_PICTURE);
            return false;
        }
        
        return true;
    }

}