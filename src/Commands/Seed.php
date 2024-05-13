<?php

namespace App\Commands;

use App\DB\Models\ContactDetails;
use App\DB\Models\PersonalDetails;
use Exception;
use App\DB\Models\User;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class Seed extends Command
{
    protected static $defaultName = 'seed';

    protected static $defaultDescription = 'Executes seed for the database.';

    protected function configure(): void
    {
        $this->setHelp(self::$defaultDescription);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        try {
            $this->seedUsers($input, $io);
            $this->seedContactDetails($input, $io);
            $this->seedPersonalDetails($input, $io);
        } catch (Exception $e) {
            if (!$input->getOption('quiet')) {
                $io->error('There was an error while running seeder: ' . $e->getMessage());
            }
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }

    private function seedUsers(InputInterface $input, SymfonyStyle $io)
    {
        $users = [];

        // @throws Exception
        $users[] = User::create([
            'username' => 'Khaled',
            'email' => 'khaled@example.com',
            'password' => password_hash('khaled123', PASSWORD_BCRYPT),
        ]);

        $users[] = User::create([
            'username' => 'Ameer',
            'email' => 'ameer@example.com',
            'password' => password_hash('ameer123', PASSWORD_BCRYPT),
        ]);

        if (count($users) !== count(array_filter($users))) {
            throw new Exception('Failed to insert records!');
        }

        if (!$input->getOption('quiet')) {
            $io->success('Records inserted successfully!');
        }
    }
    private function seedContactDetails(InputInterface $input, SymfonyStyle $io)
    {
        $users = [];

        // @throws Exception
        $users[] = ContactDetails::create([
            'user_id' => 1,
            'country' => 'Egypt',
            'country_code' => 'EG',
            'phone' => '01093824508',
            'phone_verified' => 1,

        ]);

        $users[] = ContactDetails::create([
            'user_id' => 2,
            'country' => 'Egypt',
            'country_code' => 'EG',
            'phone' => '01221222195',
            'phone_verified' => 0,

        ]);

        if (count($users) !== count(array_filter($users))) {
            throw new Exception('Failed to insert records!');
        }

        if (!$input->getOption('quiet')) {
            $io->success('Records inserted successfully!');
        }
    }
    private function seedPersonalDetails(InputInterface $input, SymfonyStyle $io)
    {
        $users = [];

        // @throws Exception
        $users[] = PersonalDetails::create([
            'user_id' => 1,
            'first_name' => 'Khaled',
            'last_name' => 'Yusuf',
            'gender' => 1,
            'birth_date' => date('Y-m-d', strtotime('-30 years')),
            'photo'=>'x.png'
        ]);


        $users[] = PersonalDetails::create([
            'user_id' => 2,
            'first_name' => 'Ameer',
            'last_name' => 'Mahmoud',
            'gender' => 2,
            'birth_date' => date('Y-m-d', strtotime('-27 years')),
            'photo'=>'A.png'
        ]);
        if (count($users) !== count(array_filter($users))) {
            throw new Exception('Failed to insert records!');
        }

        if (!$input->getOption('quiet')) {
            $io->success('Records inserted successfully!');
        }
    }
}
