<?php
namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Process\Process;
use Symfony\Component\Yaml\Yaml;

class AddHostCommand extends Command
{
    protected static $defaultName = 'app:add:host';

    protected function configure()
    {
        $this
        ->setDescription('Adds hosts to yaml host configuration file.')
        ->setHelp('There are no additional arguments for this command.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->addHost($input, $output);
    }

    protected function addHost(InputInterface & $input, OutputInterface & $output)
    {
        $filesystem = new Filesystem();
        if(!$filesystem->exists('./wakeHosts.yaml')) {
            $output->section()->writeln("<info>The file wakeHosts.yaml doesn't exist. Creating it.</info>");
            $filesystem->touch('wakeHosts.yaml');
        }

        $hostList = $this->getHostList();

        $alias = $this->askAlias($input, $output);
        if($this->getHostByAlias($alias) !== false) {
            $output->section()->writeln('<error>There is already an alias with this name. Try again.</error>');
            $this->addHost($input, $output);
        }
        $hostArray = [];
        $hostArray['hostname'] = $this->askHostname($input, $output, $alias);
        $hostArray['user'] = $this->askUsername($input, $output, $alias);
        $hostArray['password'] = $this->askPassword($input, $output);
        $hostArray['mac'] = $this->askMacAddress($input, $output);
        $hostList[$alias] = $hostArray;
        $yaml = Yaml::dump($hostList);
        $output->writeln('<info>Succesfully updated yaml!</info>');
        file_put_contents('./wakeHosts.yaml', $yaml);

        $questionString = '<question>Would you like to add another host?</question>';
        $helper = $this->getHelper('question');
        $output->section()->writeln($questionString);
        $question = new ConfirmationQuestion('(y/n): ', 'n');
        if($helper->ask($input, $output, $question)) {
            $this->addHost($input, $output);
        }
    }

    protected function askAlias(InputInterface & $input, OutputInterface & $output)
    {
        $questionString = "<question>Enter an alias for this computer. Google Assistant will be using this alias. It can be the same as it's netbios hostname.</question> ";
        $helper = $this->getHelper('question');
        $output->section()->writeln($questionString);
        $question = new Question('Alias: ', false);
        $question->setValidator(function ($answer) {
            if (empty($answer)) {
                throw new \RuntimeException('The alias should not be blank');
            }
            return $answer;
        });
        $alias = $helper->ask($input, $output, $question);
        return $alias;
    }

    protected function askHostname(InputInterface & $input, OutputInterface & $output, $alias)
    {
        $questionString = "<question>Is the alias the same as the computer's hostname? (If not you'll enter an IP address next)</question>";
        $output->section()->writeln($questionString);
        $question = new ConfirmationQuestion('(y/n): ', 'n');
        $helper = $this->getHelper('question');
        if (!$hostname = $helper->ask($input, $output, $question)) {
            $questionString = "<question>Enter the IP address or hostname? (Will be used to run a SSH shutdown command)</question>";
            $helper = $this->getHelper('question');
            $output->section()->writeln($questionString);
            $question = new Question('IP Address or Hostname: ', false);
            return $helper->ask($input, $output, $question);
        }
        return $alias;
    }

    protected function askUsername(InputInterface & $input, OutputInterface & $output)
    {
        $questionString = '<question>Enter the SSH username:</question>';
        $output->section()->writeln($questionString);
        $question = new Question('username: ', false);
        $question->setValidator(function ($answer) {
            if (empty($answer)) {
                throw new \RuntimeException('The username cannot be blank');
            }
            return $answer;
        });
        $helper = $this->getHelper('question');
        $username = $helper->ask($input, $output, $question);
        return $username;
    }

    protected function askPassword(InputInterface & $input, OutputInterface & $output)
    {
        $questionString = '<question>Enter the SSH password of this host.</question>';
        $output->section()->writeln($questionString);
        $question = new Question('password: ', false);
        $question->setHidden(true);
        $helper = $this->getHelper('question');
        $password = $helper->ask($input, $output, $question);
        return $password;
    }

    protected function askMacAddress(InputInterface & $input, OutputInterface & $output)
    {
        $questionString = '<question>Enter the MAC address of this host. Ethernet ONLY. It is impossible for wireless hosts to be remotely turned on. Leave blank for wireless.</question>';
        $output->section()->writeln($questionString);
        $question = new Question('MAC address: ', false);
        $helper = $this->getHelper('question');
        $mac = $helper->ask($input, $output, $question);
        return $mac;
    }

    protected function getHostList()
    {
        $configFile = './wakeHosts.yaml';
        return Yaml::parseFile($configFile);
    }

    protected function getHostByAlias($hostname)
    {
        $hostList = $this->getHostList();
        if(empty($hostList)) {
            return false;
        }
        foreach($this->getHostList() as $host => $values) {
            if($host == $hostname) {
                return $values;
            }
        }
        return false;
    }
}
