<?php

namespace App\Commands;

use App\DB\Models\ActivityLog;
use App\DB\Models\AdminNotifications;
use App\DB\Models\Currency;
use App\DB\Models\Wallet;
use Exception;
use Illuminate\Database\Schema\Blueprint;
use App\DB\Models\Token;
use App\DB\Models\User;
use App\DB\Models\ContactDetails;
use App\DB\Models\PersonalDetails;
use App\DB\Models\TwoFactorAuthentication;
use App\DB\Models\UserDevices;
use Slim\App;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
#[AsCommand(name: 'migrate', description: 'Executes migration for the database.')]

class Migrate extends Command
{

    protected function configure(): void
    {
        $this
            ->setHelp($this->getDescription())
            ->setDefinition(
                new InputDefinition([
                    new InputOption('fresh', null, InputOption::VALUE_NONE, 'Set the migration to remove existent tables and recreate them.'),
                ])
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        try {
            $this->migrateUsers($input, $io);
            $this->migrateJwt($input, $io);
            $this->migratePersonalDetails($input, $io);
            $this->migrateContactDetails($input, $io);
            $this->migrateUserDevices($input, $io);
            $this->migrateTwoFactorAuthentication($input, $io);
            $this->migrateUserActivityLogs($input, $io);
            $this->migrateAdminNotifications($input, $io);
//            $this->migrateCurrencies($input, $io);
//            $this->migrateWallets($input, $io);
        } catch (Exception $e) {
            if (!$input->getOption('quiet')) {
                $io->error('There was an error while running migrations: ' . $e->getMessage());
            }
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }

    private function migrateUsers(InputInterface $input, SymfonyStyle $io): void
    {
        /** @var App */
        global $app;

        $fresh = $input->getOption('fresh');

        $user = new User;

        $db = $app->getContainer()->get('db')->schema();

        if ($db->hasTable($user->getTable()) && $fresh) {
            $db->drop($user->getTable());
        }

        if (!$db->hasTable($user->getTable()) || $fresh) {
            $db->create($user->getTable(), function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('username');
                $table->string('email', 50);
                $table->tinyInteger('email_verified')->default(0);
                $table->string('password');
                $table->string('reset_code', 100)->unique()->nullable();
                $table->tinyInteger('role')->default(2);
                $table->tinyInteger('status')->default(1);
                $table->string('language', 10)->default('en');
                $table->rememberToken();
                $table->timestamps();
                $table->softDeletes();
                $table->unique(['username', 'email']);
                $table->index(['username', 'email']);
            });

            if (!$input->getOption('quiet')) {
                $io->success('Users Table created successfully!');
            }
        } else {
            if (!$input->getOption('quiet')) {
                $io->error('Users Table already exists!');
            }
        }
    }

    private function migratePersonalDetails(InputInterface $input, SymfonyStyle $io): void
    {
        /** @var App */
        global $app;

        $fresh = $input->getOption('fresh');

        $user = new PersonalDetails();

        $db = $app->getContainer()->get('db')->schema();

        if ($db->hasTable($user->getTable()) && $fresh) {
            $db->drop($user->getTable());
        }

        if (!$db->hasTable($user->getTable()) || $fresh) {
            $db->create($user->getTable(), function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->foreignId('user_id')->constrained()->onDelete('cascade');
                $table->string('first_name');
                $table->string('last_name');
                $table->tinyInteger('gender')->default(1);
                $table->date('birth_date')->nullable();
                $table->string('photo')->nullable();
                $table->softDeletes();
                $table->index('user_id');
            });

            if (!$input->getOption('quiet')) {
                $io->success('PersonalDetails Table created successfully!');
            }
        } else {
            if (!$input->getOption('quiet')) {
                $io->error('PersonalDetails Table already exists!');
            }
        }
    }
    private function migrateContactDetails(InputInterface $input, SymfonyStyle $io): void
    {
        /** @var App */
        global $app;

        $fresh = $input->getOption('fresh');

        $user = new ContactDetails();

        $db = $app->getContainer()->get('db')->schema();

        if ($db->hasTable($user->getTable()) && $fresh) {
            $db->drop($user->getTable());
        }

        if (!$db->hasTable($user->getTable()) || $fresh) {
            $db->create($user->getTable(), function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->foreignId('user_id')->constrained()->onDelete('cascade');
                $table->string('country', 50)->nullable();
                $table->string('country_code', 10)->nullable();
                $table->string('phone', 15)->nullable();
                $table->tinyInteger('phone_verified')->default(0);
                $table->string('city', 50)->nullable();
                $table->string('street', 250)->nullable();
                $table->string('postal_code', 20)->nullable();
                $table->softDeletes();
                $table->index('user_id');
//                $table->string('email', 50)->nullable();
//                $table->tinyInteger('email_verified')->default(0);
            });

            if (!$input->getOption('quiet')) {
                $io->success('ContactDetails Table created successfully!');
            }
        } else {
            if (!$input->getOption('quiet')) {
                $io->error('ContactDetails Table already exists!');
            }
        }
    }
    private function migrateTwoFactorAuthentication(InputInterface $input, SymfonyStyle $io): void
    {
        /** @var App */
        global $app;

        $fresh = $input->getOption('fresh');

        $user = new TwoFactorAuthentication();

        $db = $app->getContainer()->get('db')->schema();

        if ($db->hasTable($user->getTable()) && $fresh) {
            $db->drop($user->getTable());
        }

        if (!$db->hasTable($user->getTable()) || $fresh) {
            $db->create($user->getTable(), function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->foreignId('user_id')->constrained()->onDelete('cascade');
                $table->tinyInteger('g2f_enabled')->default(0);
                $table->string('google2fa_secret')->nullable();
                $table->softDeletes();
                $table->index('user_id');
            });

            if (!$input->getOption('quiet')) {
                $io->success('TwoFactorAuthentication Table created successfully!');
            }
        } else {
            if (!$input->getOption('quiet')) {
                $io->error('TwoFactorAuthentication Table already exists!');
            }
        }
    }
    private function migrateUserDevices(InputInterface $input, SymfonyStyle $io): void
    {
        /** @var App */
        global $app;

        $fresh = $input->getOption('fresh');

        $table = new UserDevices();

        $db = $app->getContainer()->get('db')->schema();

        if ($db->hasTable($table->getTable()) && $fresh) {
            $db->drop($table->getTable());
        }

        if (!$db->hasTable($table->getTable()) || $fresh) {
            $db->create($table->getTable(), function (Blueprint $tbl) {
                $tbl->bigIncrements('id');
                $tbl->foreignId('user_id')->constrained()->onDelete('cascade');
                $tbl->string('device_type')->nullable();
                $tbl->string('name')->nullable();
                $tbl->timestamps();
                $tbl->softDeletes();
                $tbl->index('user_id');
            });

            if (!$input->getOption('quiet')) {
                $io->success('User Devices Table created successfully!');
            }
        } else {
            if (!$input->getOption('quiet')) {
                $io->error('User Devices Table already exists!');
            }
        }
    }
    private function migrateUserActivityLogs(InputInterface $input, SymfonyStyle $io): void
    {
        /** @var App */
        global $app;

        $fresh = $input->getOption('fresh');

        $user = new ActivityLog();

        $db = $app->getContainer()->get('db')->schema();

        if ($db->hasTable($user->getTable()) && $fresh) {
            $db->drop($user->getTable());
        }

        if (!$db->hasTable($user->getTable()) || $fresh) {
            $db->create($user->getTable(), function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->foreignId('user_id')->constrained()->onDelete('cascade');
                $table->string('device_id')->nullable();
                $table->string('action')->nullable();
                $table->string('ip_address')->nullable();
                $table->string('location')->nullable();
                $table->timestamps();
                $table->softDeletes();
                $table->index(['user_id', 'device_id']);
            });

            if (!$input->getOption('quiet')) {
                $io->success('Users Devices Table created successfully!');
            }
        } else {
            if (!$input->getOption('quiet')) {
                $io->error('User Devices Table already exists!');
            }
        }
    }
    private function migrateAdminNotifications(InputInterface $input, SymfonyStyle $io): void
    {
        /** @var App */
        global $app;

        $fresh = $input->getOption('fresh');

        $table = new AdminNotifications();

        $db = $app->getContainer()->get('db')->schema();

        if ($db->hasTable($table->getTable()) && $fresh) {
            $db->drop($table->getTable());
        }

        if (!$db->hasTable($table->getTable()) || $fresh) {
            $db->create($table->getTable(), function (Blueprint $tbl) {
                $tbl->bigIncrements('id');
                $tbl->foreignId('user_id')->constrained()->onDelete('cascade');
                $tbl->string('title');
                $tbl->tinyInteger('read_status')->default(0);
                $tbl->string('click_url')->nullable();
                $tbl->string('message')->nullable();
                $tbl->timestamps();
                $tbl->softDeletes();
                $tbl->index('user_id');
            });

            if (!$input->getOption('quiet')) {
                $io->success('Admin Notifications Table created successfully!');
            }
        } else {
            if (!$input->getOption('quiet')) {
                $io->error('Admin Notifications Table already exists!');
            }
        }
    }
    private function migrateSystemNotifications(InputInterface $input, SymfonyStyle $io): void
    {
        /** @var App */
        global $app;

        $fresh = $input->getOption('fresh');

        $table = new AdminNotifications();

        $db = $app->getContainer()->get('db')->schema();

        if ($db->hasTable($table->getTable()) && $fresh) {
            $db->drop($table->getTable());
        }

        if (!$db->hasTable($table->getTable()) || $fresh) {
            $db->create($table->getTable(), function (Blueprint $tbl) {
                $tbl->bigIncrements('id');
                $tbl->string('model');
                $tbl->string('title');
                $tbl->tinyInteger('read_status')->default(0);
                $tbl->string('click_url')->nullable();
                $tbl->string('message')->nullable();
                $tbl->timestamps();
                $tbl->softDeletes();
            });

            if (!$input->getOption('quiet')) {
                $io->success('System Notifications Table created successfully!');
            }
        } else {
            if (!$input->getOption('quiet')) {
                $io->error('System Notifications Table already exists!');
            }
        }
    }
    private function migrateJwt(InputInterface $input, SymfonyStyle $io): void
    {
        /** @var App */
        global $app;

        $fresh = $input->getOption('fresh');

        $token = new Token;

        $db = $app->getContainer()->get('db')->schema();

        if ($db->hasTable($token->getTable()) && $fresh) {
            $db->drop($token->getTable());
        }

        if (!$db->hasTable($token->getTable()) || $fresh) {
            $db->create($token->getTable(), function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->foreignId('user_id')->constrained()->onDelete('cascade');
                $table->string('name', 40);
                $table->string('token', 150);
                $table->string('ip', 150)->nullable();
                $table->string('device', 150)->nullable();
                $table->integer('uses')->default(0);
                $table->integer('use_limit')->default(0);
                $table->dateTime('expire_at')->nullable();
                $table->timestamps();
                $table->softDeletes();
                $table->index('user_id');
            });

            if (!$input->getOption('quiet')) {
                $io->success('Tokens table created successfully!');
            }
        } else {
            if (!$input->getOption('quiet')) {
                $io->error('Tokens table already exists!');
            }
        }
    }

    private function migrateCurrencies(InputInterface $input, SymfonyStyle $io): void
    {
        /** @var App */
        global $app;

        $fresh = $input->getOption('fresh');

        $coin = new Currency;

        $db = $app->getContainer()->get('db')->schema();

        if ($db->hasTable($coin->getTable()) && $fresh) {
            $db->drop($coin->getTable());
        }

        if (!$db->hasTable($coin->getTable()) || $fresh) {
            $db->create($coin->getTable(), function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('symbol',20);
                $table->string('name', 40);
                $table->decimal('usd',19,8)->default(0);
                $table->tinyInteger('status')->default(1);
                $table->string('address',64);
                $table->string('image',255)->nullable();
                $table->tinyInteger('is_withdrawal',0)->default(0);
                $table->tinyInteger('is_deposit',0)->default(0);
                $table->text('description')->nullable();
                $table->timestamps();
                $table->softDeletes();
                $table->index('symbol');
            });

            if (!$input->getOption('quiet')) {
                $io->success('Currencies table created successfully!');
            }
        } else {
            if (!$input->getOption('quiet')) {
                $io->error('Currencies table already exists!');
            }
        }
    }
    private function migrateWallets(InputInterface $input, SymfonyStyle $io): void
    {
        /** @var App */
        global $app;

        $fresh = $input->getOption('fresh');

        $wallet = new Wallet();

        $db = $app->getContainer()->get('db')->schema();

        if ($db->hasTable($wallet->getTable()) && $fresh) {
            $db->drop($wallet->getTable());
        }

        if (!$db->hasTable($wallet->getTable()) || $fresh) {
            $db->create($wallet->getTable(), function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->foreignId('user_id')->constrained()->onDelete('cascade');
                $table->foreignId('currency_id')->constrained()->onDelete('cascade');
                $table->string('chain', 20);
                $table->decimal('balance', 19, 8)->default(0);
                $table->string('address', 64);
                $table->string('activation_trx', 64)->nullable();
                $table->string('network', 50)->nullable();
                $table->tinyInteger('status')->default(0);
                $table->timestamps();
                $table->softDeletes();
                $table->unique(['user_id', 'currency_id']);
                $table->index(['user_id', 'currency_id']);
            });

            if (!$input->getOption('quiet')) {
                $io->success('Wallets table created successfully!');
            }
        } else {
            if (!$input->getOption('quiet')) {
                $io->error('Wallets table already exists!');
            }
        }
    }
}
