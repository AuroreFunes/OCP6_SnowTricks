<?php

namespace App\Test;

use App\Entity\Trick;
use App\Entity\TrickComment;
use App\Entity\TrickGroup;
use App\Entity\TrickHistory;
use App\Entity\TrickImage;
use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class EntityTest extends KernelTestCase
{

    /**
     * @var \Doctrine\ORM\EntityManager
     */
    private $entityManager;

    protected function setUp(): void
    {
        $kernel = self::bootKernel();

        $this->entityManager = $kernel->getContainer()
            ->get('doctrine')
            ->getManager();
    }

    public function testUserEntity()
    {
        // number of user before create new one
        $userNumber = count($this->entityManager->getRepository(User::class)->findAll());

        $user = new User();
        $user
            ->setUsername('test' . uniqid())
            ->setCreatedAt(new \DateTime())
            ->setEmail(uniqid() . '_test@testmail.test')
            ->setPassword('123Test!')
            ->setIsActive(true);

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        // we need to have one more user
        $this->assertCount($userNumber + 1, $this->entityManager->getRepository(User::class)->findAll());
    }


    public function testTrickEntity()
    {
        $trickNumber = count($this->entityManager->getRepository(Trick::class)->findAll());
        $commentNumber = count($this->entityManager->getRepository(TrickComment::class)->findAll());

        $user = $this->entityManager->getRepository(User::class)->find(1);
        $trickGroup = $this->entityManager->getRepository(TrickGroup::class)->find(1);

        // create one trick with one comment, and add history
        $trick = new Trick();
        $trick
            ->setName('figure test ' . uniqid())
            ->setDescription('courte description pour figure test')
            ->setSlug('figure-test-' . uniqid());

        $trickImg = new TrickImage();
        $trickImg
                ->setPath('default.jpg')
                ->setIsDefault(true);

        $trickHistory = new TrickHistory();
        $trickHistory
                ->setAuthor($user)
                ->setTrick($trick)
                ->setDate(new \DateTime());

        $trick->addImage($trickImg);
        $trick->addHistory($trickHistory);
        $trickGroup->addTrick($trick);

        $comment = new TrickComment();
        $comment
            ->setAuthor($user)
            ->setCreated(new \DateTime())
            ->setContent('voici un commentaire de test');
        
        $trick->addComment($comment);

        $this->entityManager->persist($trick);
        $this->entityManager->flush();

        // count tricks and comment : we need one more
        $this->assertCount($trickNumber + 1, $this->entityManager->getRepository(Trick::class)->findAll());
        $this->assertCount($commentNumber + 1, $this->entityManager->getRepository(TrickComment::class)->findAll());

        // now remove trick. The comment will be deleted automatically
        $this->entityManager->remove($trick);
        $this->entityManager->flush();

        // count trick and comment to check
        $this->assertCount($trickNumber, $this->entityManager->getRepository(Trick::class)->findAll());
        $this->assertCount($commentNumber, $this->entityManager->getRepository(TrickComment::class)->findAll());
    }
}