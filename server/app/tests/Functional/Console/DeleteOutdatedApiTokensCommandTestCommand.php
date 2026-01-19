<?php

declare(strict_types=1);

namespace App\Tests\Functional\Console;

use App\DataFixtures\AppFixtures;
use App\Repository\ApiTokenRepository;
use App\Tests\Tools\FunctionalTestingToolTrait;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;

class DeleteOutdatedApiTokensCommandTestCommand extends KernelTestCase
{
    use FunctionalTestingToolTrait;

    public function testDeleteOutdatedApiTokens(): void
    {
        self::bootKernel();
        $application = new Application(self::$kernel);

        $apiToken = $this->createExpiredUserApiToken(AppFixtures::FIXTURE_USER_EMAIL);

        // How many tokens do we have in the DB now?
        $apiTokenRepository = static::getContainer()->get(ApiTokenRepository::class);
        $tokensCount = count($apiTokenRepository->findAll());

        $entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $entityManager->clear();

        // Run the command
        $command = $application->find('app:delete-outdated-api-tokens');
        $commandTester = new CommandTester($command);
        $commandTester->execute([]);
        $commandTester->assertCommandIsSuccessful();

        static::assertCount($tokensCount - 1, $apiTokenRepository->findAll());
        static::assertNull($apiTokenRepository->find($apiToken->getId()));
    }
}
