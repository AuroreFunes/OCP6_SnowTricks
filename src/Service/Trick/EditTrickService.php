<?php

namespace App\Service\Trick;

use App\Entity\Trick;
use App\Entity\TrickGroup;
use App\Entity\TrickHistory;
use App\Entity\TrickImage;
use App\Entity\TrickVideo;
use App\Entity\User;
use App\Service\PictureServiceHelper;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class EditTrickService extends PictureServiceHelper
{
    protected const ERR_TRICK_EMPTY = "La figure n'est pas définie.";
    protected const ERR_USER_UNKNOWN = "L'utilisateur doit être connecté.";
    protected const ERR_UNABLE_TO_ADD_MAIN_IMAGE = "L'image par défaut n'a pas pu être ajoutée.";

    protected string $projectDir;


    public function __construct(ManagerRegistry $manager, string $projectDir)
    {
        parent::__construct($manager, $projectDir);

        $this->projectDir = $projectDir . '\public\img\tricks';
    }


    // ============================================================================================
    // ENTRYPOINT
    // ============================================================================================
    public function editTrick(
        ?Trick $trick, 
        ?User $user, 
        ?array $pictures,
        ?UploadedFile $defaultPicture,
        ?string $videosLinks
    ): self
    {
        $this->initHelper();

        // save parameters
        $this->functArgs->set('trick', $trick);
        $this->functArgs->set('user', $user);
        $this->functArgs->set('defaultPicture', $defaultPicture);
        $this->functArgs->set('pictures', $pictures);
        $this->functArgs->set('videos', $videosLinks);

        if (false === $this->checkParameters()) {
            return $this;
        }

        if (false === $this->setTrick()) {
            return $this;
        }

        // images must be added even if the default image has not been added
        $statusOk = true;

        if (false === $this->setDefaultPicture()) {
            $statusOk = false;
        }

        if (false === $this->addTrickImages()) {
            $statusOk = false;
        }

        if (false === $this->addVideos()) {
            $statusOk = false;
        }

        $this->status = $statusOk;
        return $this;
    }

    // ============================================================================================
    // JOBS
    // ============================================================================================
    protected function setTrick(): bool
    {
        // create slug
        $this->functArgs->get('trick')->setSlug($this->createSlug());

        // create history
        $trickHistory = new TrickHistory();
        $trickHistory
            ->setAuthor($this->functArgs->get('user'))
            ->setTrick($this->functArgs->get('trick'))
            ->setDate(new \DateTime());

        $this->functArgs->get('trick')->addHistory($trickHistory);

        // save trick
        try {
            $this->manager->persist($this->functArgs->get('trick'));
            $this->manager->flush();
        } catch (\Exception $e) {
            $this->errMessages->add(self::ERR_DB_ACCESS);
            return false;
        }

        $this->functResult->set('trickId', $this->functArgs->get('trick')->getId());

        return true;
    }

    protected function setDefaultPicture(): bool
    {
        if (null === $this->functArgs->get('defaultPicture')) {
            // do not change default picture
            return true;
        }

        if (false === $this->checkPicture($this->functArgs->get('defaultPicture'))) {
            // invalid picture !
            return false;
        }

        // save default picture
        $pictureName = $this->functArgs->get('trick')->getId() . "_trickPicture_" . uniqid();

        if (false === $this->savePicture(
            $this->functArgs->get('defaultPicture'),
            $this->projectDir,
            $pictureName
            )
        ) {
            return false;
        }

        // add default picture
        $defaultPicture = new TrickImage();
        $defaultPicture
            ->setPath($pictureName . "." . $this->functArgs->get('defaultPicture')->getClientOriginalExtension())
            ->setTrick($this->functArgs->get('trick'));

        $this->functArgs->get('trick')->setDefaultImage($defaultPicture);

        // save default picture in database
        try {
            $this->manager->persist($defaultPicture);
            $this->manager->persist($this->functArgs->get('trick'));
            $this->manager->flush();
        } catch (\Exception $e) {
            $this->errMessages->add(self::ERR_DB_ACCESS);
            return false;
        }

        return true;
    }

    protected function addTrickImages(): bool
    {
        foreach ($this->functArgs->get('pictures') as $picture) {
            // check picture
            if (false === $this->checkPicture($picture)) {
                continue;
            }

            // save picture
            $pictureName = $this->functArgs->get('trick')->getId() . "_trickPicture_" . uniqid();

            if (false === $this->savePicture(
                $picture,
                $this->projectDir,
                $pictureName
                )
            ) {
                continue;
            }

            // add picture in trick
            $trickPicture = new TrickImage();
            $trickPicture
                ->setPath($pictureName . "." . $picture->getClientOriginalExtension())
                ->setTrick($this->functArgs->get('trick'))
                ->setIsDefault(false);
            $this->functArgs->get('trick')->addImage($trickPicture);

            // persist
            $this->manager->persist($trickPicture);
        }

        // now save in database
        try {
            $this->manager->persist($this->functArgs->get('trick'));
            $this->manager->flush();
        } catch (\Exception $e) {
            $this->errMessages->add(self::ERR_DB_ACCESS);
            return false;
        }

        return true;
    }

    protected function addVideos(): bool
    {
        if (empty($this->functArgs->get('videos'))) {
            return true;
        }

        $videos = explode("|", $this->functArgs->get('videos'));

        foreach ($videos as $video) {

            // check url
            if (false === filter_var(trim($video), FILTER_VALIDATE_URL)) {
                continue;
            }

            $trickVideo = new TrickVideo();
            $trickVideo
                ->setPath(trim($video))
                ->setTrick($this->functArgs->get('trick'));
            
            $this->functArgs->get('trick')->addVideo($trickVideo);

            $this->manager->persist($trickVideo);
        }

        $this->manager->persist($this->functArgs->get('trick'));

        try {
            $this->manager->flush();
        } catch (\Exception $e) {
            $this->errMessages->add(self::ERR_DB_ACCESS);
            return false;
        }

        return true;
    }

    protected function createSlug(): string
    {
        $title = trim(strtolower($this->functArgs->get('trick')->getName()));

        // create slug
        $patterns = [
            "~ ~",
            "~'~",
            "~[^a-z0-9-]~",
            "~--~"
        ];

        $replace = [
            "-",
            "-",
            "",
            ""
        ];

        return preg_replace($patterns, $replace, $title);
    }

    // ============================================================================================
    // CHECK PARAMETERS
    // ============================================================================================
    protected function checkParameters(): bool
    {
        if (null === $this->functArgs->get('trick')) {
            $this->errMessages->add(self::ERR_TRICK_EMPTY);
            return false;
        }

        if (null === $this->functArgs->get('user')) {
            $this->errMessages->add(self::ERR_USER_UNKNOWN);
            return false;
        }

        // the parameters of the trick have been checked by the form

        return true;
    }


    // ============================================================================================
    // TOOLS
    // ============================================================================================
    protected function initHelper(): void
    {
        parent::initHelper();
    }

}