<?php

namespace App\Commands;

use Ilex\SwoolePsr7\SwooleResponseConverter;
use OpenSwoole\Http\Request;
use OpenSwoole\Http\Response;
use OpenSwoole\HTTP\Server;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Attribute\AsCommand;
#[AsCommand(name: 'http-server', description: 'Start the HTTP server')]
class HttpServer extends Command
{

    protected static $defaultDescription = 'Starts Http Server';

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
        global $app, $requestConverter;

        $server = new Server("0.0.0.0", 8080);


        $server->on("start", function(Server $server) use ($io) {
            $io->success("HTTP Server ready at http://127.0.0.1:8080");
        });

        $server->on('request', function(Request $request, Response $response) use ($app, $requestConverter) {
            $psr7Request = $requestConverter->createFromSwoole($request);
            $psr7Response = $app->handle($psr7Request);
            $converter = new SwooleResponseConverter($response);
            $converter->send($psr7Response);
        });

//        // Triggered when new worker processes starts
//        $server->on("WorkerStart", function($server, $workerId)
//        {
//            // ...
//        });

//        // Triggered when the server is shutting down
//        $server->on("Shutdown", function($server, $workerId)
//        {
//            // ...
//        });
//
//        // Triggered when worker processes are being stopped
//        $server->on("WorkerStop", function($server, $workerId)
//        {
//            // ...
//        });

        $server->set([
            'document_root' => ROOT_DIR . '/public',
            'enable_static_handler' => true,
            'static_handler_locations' => ['/js'],
            'worker_num' => 4,
//            'task_worker_num' => 4,
            'backlog' => 128,
        ]);

        $server->start();
    }
}
