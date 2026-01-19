<?php

declare(strict_types=1);

namespace App\Tests\Functional\Console;

use App\DataFixtures\AppFixtures;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;

class CreateUserCommandTest extends KernelTestCase
{
    public function testCreateUser(): void
    {
        $userEmail = 'foo@baar.com';

        self::bootKernel();
        $application = new Application(self::$kernel);

        // Test that the user does not exist.
        $userRepository = static::getContainer()->get(UserRepository::class);
        static::assertNull($userRepository->findOneBy(['email' => $userEmail]));

        // Create it.
        $command = $application->find('app:create-user');
        $commandTester = new CommandTester($command);
        $commandTester->execute(['email' => $userEmail, 'password' => 'bar']);
        $commandTester->assertCommandIsSuccessful();

        // Test it is created in the DB.
        static::assertNotNull($userRepository->findOneBy(['email' => $userEmail]));
    }

    public function testUserAlreadyExists(): void
    {
        $userEmail = AppFixtures::FIXTURE_USER_EMAIL;

        self::bootKernel();
        $application = new Application(self::$kernel);

        // Test that the user does not exist.
        $userRepository = static::getContainer()->get(UserRepository::class);
        static::assertNotNull($userRepository->findOneBy(['email' => $userEmail]));

        // Create it.
        $command = $application->find('app:create-user');
        $commandTester = new CommandTester($command);
        $returnCode = $commandTester->execute(['email' => $userEmail, 'password' => 'bar']);
        static::assertSame(1, $returnCode);
        $output = $commandTester->getDisplay();
        static::assertStringContainsString(" A user with the email '$userEmail' already exists.", $output);
    }
}
