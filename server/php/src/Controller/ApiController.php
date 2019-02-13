<?php
namespace App\Controller;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Yaml\Yaml;

class ApiController extends Controller
{
    /**
     * @Route("/api/boot/{alias}/{interface}")
     */
    public function boot($alias, $interface = 'eth0')
    {
        if(!$host = $this->getHostByAlias($alias))
        {
            return new Response('Could not find host ' . $alias);
        }

        $process = new Process(['etherwake', '-i', $interface, $host['mac']]);
        $process->run();

        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        return new Response($process->getOutput());
    }

    /**
     * @Route("/api/shutdown/{alias}")
     */
    public function shutdown($alias)
    {
        if(!$host = $this->getHostByAlias($alias))
        {
            return new Response('Could not find host ' . $alias);
        }
        $command = 'ssh -tt ' . $host['user'] .'@' . $host['hostname'] . ' "echo ' . $host['password'] . ' | sudo -S shutdown -h now"';
        $process = new Process($command);
        $process->run();

        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        return new Response($process->getOutput());
    }

    protected function getHostList()
    {
        $configFile = $this->get('kernel')->getProjectDir() . '/../../wakeHosts.yaml';
        return Yaml::parseFile($configFile);
    }

    protected function getHostByAlias($search)
    {
        foreach($this->getHostList() as $alias => $values) {
            if($alias == $search) {
                return $values;
            }
        }
        return false;
    }
}
