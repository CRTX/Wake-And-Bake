<?php
namespace App\Command;

use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;
use Symfony\Component\Yaml\Yaml;

class AuthorizeCommand extends Command
{
    protected static $defaultName = 'app:ssh:authorize';

    protected function configure()
    {
        $this
        ->setDescription("Copies SSH public key (id_rsa.pub) to all remote host's local ~/.ssh/authorized_keys file.")
        ->setHelp('There are no additional arguments for this command.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $command = $this->getApplication()->find('app:ssh:update-known-hosts');
        $arrayInput = new ArrayInput([]);
        $returnCode = $command->run($arrayInput, $output);

        $output->section()->writeln('<info>Attempting to copy id_rsa.pub key to all known hosts...</info>');
        foreach($this->getHostList() as $host => $values) {
            $command = "sshpass -p $values[password] ssh-copy-id -i ~/.ssh/id_rsa.pub $values[user]@$values[hostname]";
            $process = new Process($command);
            $process->run();
            $output->section()->writeln($process->getOutput());
        }
    }

    protected function getHostList()
    {
        $configFile = './wakeHosts.yaml';
        return Yaml::parseFile($configFile);
    }
}
