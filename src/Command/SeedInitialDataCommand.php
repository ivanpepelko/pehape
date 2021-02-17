<?php

namespace App\Command;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class SeedInitialDataCommand extends Command
{
    private const USERS = [
        ['ivanpepelko', 'godmode', ['ROLE_ADMIN', 'ROLE_SUPERADMIN']],
        ['admin', 'php5', ['ROLE_ADMIN']],
        ['user', 'user', ['ROLE_USER']],
    ];

    protected static $defaultName = 'app:seed-initial-data';

    private EntityManagerInterface $entityManager;

    private UserPasswordEncoderInterface $passwordEncoder;

    public function __construct(EntityManagerInterface $entityManager, UserPasswordEncoderInterface $passwordEncoder)
    {
        parent::__construct();
        $this->entityManager = $entityManager;
        $this->passwordEncoder = $passwordEncoder;
    }

    protected function configure(): void
    {
        $this->setDescription('Seed initial data into database')
             ->addOption('drop', null, InputOption::VALUE_NONE, 'Drop all data before seeding');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        if ($input->getOption('drop')) {
            $this->dropData($io);
        }

        $this->seedUsers($io);

        return Command::SUCCESS;
    }

    private function dropData(SymfonyStyle $io): void
    {
        $answer = $io->confirm('--complete is defined, drop all data?', false);

        if (!$answer) {
            return;
        }

        $userRepository = $this->entityManager->getRepository(User::class);
        $count = $userRepository->count([]);
        if ($count < 1) {
            $io->info(sprintf('No entities of type %s found', User::class));
        }

        $io->info(sprintf('Deleting all entities of type %s', User::class));
        $progress = new ProgressBar($io, $count);
        foreach ($progress->iterate($userRepository->findAll()) as $user) {
            $this->entityManager->remove($user);
        }
        $this->entityManager->flush();

        $io->info(sprintf('All %s entities deleted.', User::class));
    }

    private function seedUsers(SymfonyStyle $io): void
    {
        foreach (self::USERS as $userData) {
            [$username, $password, $roles] = $userData;

            $io->info("Seeding $username...");
            $user = $this->entityManager->getRepository(User::class)->findOneBy(['username' => $username]);

            if ($user !== null) {
                $io->info("User $username exists");
                continue;
            }

            $user = new User();
            $user->setUsername($username)
                 ->setRoles($roles);

            $user->setPassword($this->passwordEncoder->encodePassword($user, $password));

            $this->entityManager->persist($user);
        }

        $this->entityManager->flush();
        $io->success('All users seeded');
    }
}
