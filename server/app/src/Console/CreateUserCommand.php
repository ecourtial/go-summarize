<?php

declare(strict_types=1);

namespace App\Console;

use App\Exception\DuplicateUserException;
use App\Service\UserService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'app:create-user', description: 'Create a user.')]
final class CreateUserCommand extends Command
{
    private const string USER_EMAIL_PARAM = 'email';
    private const string USER_PASSWORD_PARAM = 'password';

    public function __construct(private readonly UserService $userService)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $email = $input->getArgument(self::USER_EMAIL_PARAM);
        $password = $input->getArgument(self::USER_PASSWORD_PARAM);

        /** @var QuestionHelper $helper */
        $helper = $this->getHelper('question');

        if (!$email) {
            $emailQuestion = new Question('Please enter the user email: ');
            $email = $helper->ask($input, $output, $emailQuestion);
        }

        if (!$password) {
            $passwordQuestion = new Question('Please enter the password: ');
            $passwordQuestion->setHidden(true);
            $passwordQuestion->setHiddenFallback(false); // Fails if hiding not supported
            $password = $helper->ask($input, $output, $passwordQuestion);
        }

        if (!is_string($email) || '' === trim($email) || !is_string($password) || '' === trim($password)) {
            $io->error('Email and password are required.');

            return Command::FAILURE;
        }

        try {
            $user = $this->userService->createUser($email, $password);
            $io->success("User has been created. Id is #{$user->getId()}.");

            return Command::SUCCESS;
        } catch (DuplicateUserException $e) {
            $io->error($e->getMessage());

            return Command::FAILURE;
        }
    }

    protected function configure(): void
    {
        $this->addArgument(self::USER_EMAIL_PARAM, InputArgument::OPTIONAL, 'User email');
        $this->addArgument(self::USER_PASSWORD_PARAM, InputArgument::OPTIONAL, 'User password');
    }
}
