<?php
namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

class GenerateKeyCommand extends Command
{
    protected static $defaultName = 'app:ssh:generate';

    protected function configure()
    {
        $this
        ->setDescription('Generates a SSH key for this computer.')
        ->setHelp('There are no additional arguments for this command.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->section()->writeln('<info>Generating SSH key. Might take a few moments...</info>');
        $command = "ssh-keygen -t rsa -b 4096 -N '' -f ~/.ssh/id_rsa";
        $process = new Process($command);
        $process->run();
        $output->section()->writeln($process->getOutput());
    }
}
