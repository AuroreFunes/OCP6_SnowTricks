<?php

namespace App\DataFixtures;

use App\Entity\Trick;
use App\Entity\TrickGroup;
use App\Entity\TrickHistory;
use App\Entity\TrickImage;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

use App\Entity\User;

class AllFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        // ========================================================================================
        // USER
        // ========================================================================================
        $user = new User();
        $user->setUsername('Admin');
        $user->setEmail('admin@mysitetest.com');

        //$passwordHasher = new UserPasswordHasherInterface();
        //$user->setPassword($passwordHasher->hashPassword($user, '123@dmin!'));

        $user->setPassword(password_hash('123@dmin!', PASSWORD_DEFAULT));
        $user->setCreatedAt(new \DateTime());
        $user->setIsActive(true);
        $user->setProfilePicture('default.jpg');
        $manager->persist($user);

        // ========================================================================================
        // TRICKS : Grabs
        // ========================================================================================
        $trickGroup = new TrickGroup();
        $trickGroup->setName('Grabs');

        // TRICK : mute
        $trick = new Trick();
        $trick
                ->setName('Mute')
                ->setDescription('Le mute consiste à saisir la carre frontside de la planche entre 
les deux pieds avec la main avant.')
                ->setSlug('mute');

        $trickImg = new TrickImage();
        $trickImg
                ->setPath('mute.jpg')
                ->setIsDefault(true);

        $trickHistory = new TrickHistory();
        $trickHistory
                ->setAuthor($user)
                ->setTrick($trick)
                ->setDate(new \DateTime());

        $trick->addImage($trickImg);
        $trick->addHistory($trickHistory);
        $trickGroup->addTrick($trick);

        // TRICK : indy
        $trick = new Trick();
        $trick
                ->setName('Indy')
                ->setDescription('L\'indy consiste à saisir la carre frontside de la planche, entre 
les deux pieds, avec la main arrière.')
                ->setSlug('indy');

        $trickImg = new TrickImage();
        $trickImg
                ->setPath('indy.jpg')
                ->setIsDefault(true);

        $trickHistory = new TrickHistory();
        $trickHistory
                ->setAuthor($user)
                ->setTrick($trick)
                ->setDate(new \DateTime());

        $trick->addImage($trickImg);
        $trick->addHistory($trickHistory);
        $trickGroup->addTrick($trick);

        // TRICK : stalefish
        $trick = new Trick();
        $trick
                ->setName('Stalefish')
                ->setDescription('Le stalefish consiste à saisir la carre backside de la planche 
entre les deux pieds avec la main arrière')
                ->setSlug('stalefish');

        $trickImg = new TrickImage();
        $trickImg
                ->setPath('stalefish.jpg')
                ->setIsDefault(true);

        $trickHistory = new TrickHistory();
        $trickHistory
                ->setAuthor($user)
                ->setTrick($trick)
                ->setDate(new \DateTime());

        $trick->addImage($trickImg);
        $trick->addHistory($trickHistory);
        $trickGroup->addTrick($trick);

        $manager->persist($trickGroup);

        // ========================================================================================
        // TRICKS : Rotations
        // ========================================================================================

        $trickGroup = new TrickGroup();
        $trickGroup->setName('Rotations');

        // TRICK : 180
        $trick = new Trick();
        $trick
                ->setName('180')
                ->setDescription('Un 180 désigne un demi-tour, soit 180 degrés d\'angle')
                ->setSlug('180');

        $trickImg = new TrickImage();
        $trickImg
                ->setPath('180.jpg')
                ->setIsDefault(true);

        $trickHistory = new TrickHistory();
        $trickHistory
                ->setAuthor($user)
                ->setTrick($trick)
                ->setDate(new \DateTime());

        $trick->addImage($trickImg);
        $trick->addHistory($trickHistory);
        $trickGroup->addTrick($trick);

        // TRICK : 360
        $trick = new Trick();
        $trick
                ->setName('360')
                ->setDescription('On dit aussi "trois six". Il consiste à faire un tour complet.')
                ->setSlug('360');

        $trickImg = new TrickImage();
        $trickImg
                ->setPath('360.jpg')
                ->setIsDefault(true);

        $trickHistory = new TrickHistory();
        $trickHistory
                ->setAuthor($user)
                ->setTrick($trick)
                ->setDate(new \DateTime());

        $trick->addImage($trickImg);
        $trick->addHistory($trickHistory);
        $trickGroup->addTrick($trick);

        // TRICK : 1080
        $trick = new Trick();
        $trick
                ->setName('1080')
                ->setDescription('On l\'appelle aussi "big foot". Il consiste à faire trois tours.')
                ->setSlug('1080');

        $trickImg = new TrickImage();
        $trickImg
                ->setPath('1080.jpg')
                ->setIsDefault(true);

        $trickHistory = new TrickHistory();
        $trickHistory
                ->setAuthor($user)
                ->setTrick($trick)
                ->setDate(new \DateTime());

        $trick->addImage($trickImg);
        $trick->addHistory($trickHistory);
        $trickGroup->addTrick($trick);

        $manager->persist($trickGroup);
        
        // ========================================================================================
        // TRICKS : Flips
        // ========================================================================================
        
        $trickGroup = new TrickGroup();
        $trickGroup->setName('Flips');

        // TRICK : Front flip
        $trick = new Trick();
        $trick
                ->setName('Front flip')
                ->setDescription('Il s\'agit d\'une roration verticale vers l\'avant.')
                ->setSlug('front-flip');

        $trickImg = new TrickImage();
        $trickImg
                ->setPath('front_flip.jpg')
                ->setIsDefault(true);

        $trickHistory = new TrickHistory();
        $trickHistory
                ->setAuthor($user)
                ->setTrick($trick)
                ->setDate(new \DateTime());

        $trick->addImage($trickImg);
        $trick->addHistory($trickHistory);
        $trickGroup->addTrick($trick);
        
        // TRICK : Back flip
        $trick = new Trick();
        $trick
                ->setName('Back flip')
                ->setDescription('Il s\'agit d\'une roration verticale vers l\'arrière.')
                ->setSlug('back-flip');

        $trickImg = new TrickImage();
        $trickImg
                ->setPath('back_flip.jpg')
                ->setIsDefault(true);

        $trickHistory = new TrickHistory();
        $trickHistory
                ->setAuthor($user)
                ->setTrick($trick)
                ->setDate(new \DateTime());

        $trick->addImage($trickImg);
        $trick->addHistory($trickHistory);
        $trickGroup->addTrick($trick);

        $manager->persist($trickGroup);

        // ========================================================================================
        // TRICKS : Slides
        // ========================================================================================

        $trickGroup = new TrickGroup();
        $trickGroup->setName('Slides');

        // TRICK : Slide
        $trick = new Trick();
        $trick
                ->setName('Slide')
                ->setDescription('Un slide consiste à glisser sur une barre de slide. Le slide se 
fait soit avec la planche dans l\'axe de la barre, soit perpendiculaire, soit plus ou moins désaxé.

On peut slider avec la planche centrée par rapport à la barre (celle-ci se situe approximativement 
au-dessous des pieds du rideur), mais aussi en nose slide, c\'est-à-dire l\'avant de la planche 
sur la barre, ou en tail slide, l\'arrière de la planche sur la barre.')
                ->setSlug('slide');

        $trickImg = new TrickImage();
        $trickImg
                ->setPath('slide.jpg')
                ->setIsDefault(true);

        $trickHistory = new TrickHistory();
        $trickHistory
                ->setAuthor($user)
                ->setTrick($trick)
                ->setDate(new \DateTime());

        $trick->addImage($trickImg);
        $trick->addHistory($trickHistory);
        $trickGroup->addTrick($trick);

        $manager->persist($trickGroup);

        // ========================================================================================
        // TRICKS : One foot tricks
        // ========================================================================================

        $trickGroup = new TrickGroup();
        $trickGroup->setName('One foot tricks');

        // TRICK : Slide
        $trick = new Trick();
        $trick
                ->setName('One foot trick')
                ->setDescription('Figure réalisée avec un pied décroché de la fixation, afin de 
tendre la jambe correspondante pour mettre en évidence le fait que le pied n\'est pas fixé. Ce 
type de figure est extrêmement dangereuse pour les ligaments du genou en cas de mauvaise 
réception.')
                ->setSlug('one-foot-trick');

        $trickImg = new TrickImage();
        $trickImg
                ->setPath('one_foot_trick.jpg')
                ->setIsDefault(true);

        $trickHistory = new TrickHistory();
        $trickHistory
                ->setAuthor($user)
                ->setTrick($trick)
                ->setDate(new \DateTime());

        $trick->addImage($trickImg);
        $trick->addHistory($trickHistory);
        $trickGroup->addTrick($trick);

        $manager->persist($trickGroup);

        $manager->flush();
    }
}
