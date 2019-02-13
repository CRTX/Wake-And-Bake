<?php
namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Process\Process;
use Symfony\Component\Yaml\Yaml;

class UpdateKnownHostsCommand extends Command
{
    protected static $defaultName = 'app:ssh:update-known-hosts';

    protected function configure()
    {
        $this
        ->setDescription('Update "known_hosts" file to keep ssh from stalling on subsequent API/SSH calls.')
        ->setHelp('There are no additional arguments for this command.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        foreach($this->getHostList() as $host => $values) {
            $command = "ssh-keyscan $values[hostname]";
            $process = new Process($command);
            $process->run();
            $outputString = $process->getOutput();
            $output->section()->writeln("<info>Appending to file ~/.ssh/known_hosts</info>");
            $output->section()->writeln($outputString);
            $filesystem = new Filesystem();
            $filesystem->appendToFile('./ssh/known_hosts', $outputString);
        }
    }

    protected function getHostList()
    {
        $configFile = './wakeHosts.yaml';
        return Yaml::parseFile($configFile);
    }
}
