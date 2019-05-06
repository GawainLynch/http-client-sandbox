<?php

namespace App\Command;

use App\Sandbox;
use Psr\Log\LogLevel;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Logger\ConsoleLogger;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class SandboxHttpClientCommand extends Command
{
    protected static $defaultName = 'sandbox:http-client';

    private $verbosityLevelMap = [
        LogLevel::NOTICE => OutputInterface::VERBOSITY_NORMAL,
        LogLevel::INFO   => OutputInterface::VERBOSITY_NORMAL,
    ];

    protected function configure()
    {
        $this
            ->setDescription('Run a sandbox test-case on the HTTP Client component')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $logger = new ConsoleLogger($output, $this->verbosityLevelMap);
        $sandbox = new Sandbox($logger);
        $client = $sandbox->createClient();
        $result = $sandbox->run($client);

        foreach ($result['fail'] as $fail => $value) {
            /** @var \Exception $value */
            $io->error(['Failed on request to: ' . $fail, 'Exception message:' . $value->getPrevious()->getMessage()]);
        }

        $io->success('Done.');
    }
}
