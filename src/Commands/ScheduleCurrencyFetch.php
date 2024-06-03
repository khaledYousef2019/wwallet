<?php

namespace App\Commands;

use Swoole\Timer;
use App\DB\Models\Currency;
use App\Events\FetchCurrencyInfo;
use App\Services\Events;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class ScheduleCurrencyFetch extends Command
{
    protected static $defaultName = 'schedule-currency-fetch';

    protected static $defaultDescription = 'Schedules currency info fetching every 20 seconds';

    protected function configure(): void
    {
        $this->setHelp(self::$defaultDescription);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $this->start($io);

        return Command::SUCCESS;
    }

    private function start(SymfonyStyle $io)
    {
        $currencies = Currency::all(); // Assuming you have a Currency model
        $interval = 20000; // 20 seconds in milliseconds
        $delay = 0;

        foreach ($currencies as $currency) {
            Timer::after($delay, function() use ($currency) {
                Events::dispatch(new FetchCurrencyInfo($currency->coin_address));
            });
            $delay += $interval;
        }

        $io->success("Currency fetch scheduling started.");
    }
}
?>
