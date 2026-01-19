<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Feed;
use App\Entity\Page;
use App\Enum\PageStatus;
use App\Service\UserService;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public const string FIXTURE_USER_EMAIL = 'foo@bar.com';
    public const string FIXTURE_USER_PASSWORD = 'foofoo123';
    public const string FIXTURE_USER_TOKEN = '3wklZ6RlHFWxRwIELXuqMHdme0aJeZw6yiv7Z50YvoXwubBVz75w9VyxrvPxeDW7';

    public function __construct(private readonly UserService $userService)
    {
    }

    public function load(ObjectManager $manager): void
    {
        $feed1 = new Feed();
        $feed1->name = 'Dynamic-Mess';
        $feed1->url = 'https://www.dynamic-mess.com/rss.xml';
        $feed1->lastFetchedAt = new \DateTime('2026-01-01 00:00:00');

        $feed2 = new Feed();
        $feed2->name = 'Some other feed';
        $feed2->url = 'http://localhost/bad-url';
        $feed2->lastFetchedAt = new \DateTime('2026-01-16 00:00:00');

        $url1 = new Page();
        $url1->feed = $feed1;
        $url1->url = 'https://www.dynamic-mess.com/windows/icone-wamp-orange-1-14/';
        $url1->title = 'Souci avec Wamp';
        $url1->description = 'Nous sommes en 2011 et vous avez un souci avec Wamp ? Regardez-ça !';
        $url1->status = PageStatus::DONE;
        $url1->publishedAt = new \DateTime();
        $url1->processedAt = new \DateTime();

        $url2 = new Page();
        $url2->feed = $feed1;
        $url2->url = 'https://www.dynamic-mess.com/reseau/nra-dslam-degroupage-18-99/';
        $url2->title = 'Le jargon des FAI';
        $url2->description = 'Votre connexion ne marche plus et vous ne comprenez rien à ce que vous dit le technicien !';
        $url2->status = PageStatus::TO_READ;
        $url2->publishedAt = new \DateTime();

        $url3 = new Page();
        $url3->feed = $feed2;
        $url3->url = 'https://www.dynamic-mess.com/virtualisation/differents-parametres-connexion-virtualbox/';
        $url3->title = 'Paramétrer VirtualBox';
        $url3->description = "Trop d'options dans Virtual Box !";
        $url3->status = PageStatus::WAITING_FOR_DECISION;
        $url3->publishedAt = new \DateTime();

        $url4 = new Page();
        $url4->feed = $feed1;
        $url4->url = 'https://www.dynamic-mess.com/foo/bar/';
        $url4->title = 'Foo Bar';
        $url4->description = 'Voici une page !';
        $url4->status = PageStatus::TO_SUMMARIZE;
        $url4->publishedAt = new \DateTime();

        $manager->persist($feed1);
        $manager->persist($feed2);
        $manager->persist($url1);
        $manager->persist($url2);
        $manager->persist($url3);
        $manager->persist($url4);

        // Will flush
        $this->userService->createUser(
            self::FIXTURE_USER_EMAIL,
            self::FIXTURE_USER_PASSWORD,
            self::FIXTURE_USER_TOKEN
        );
    }
}
