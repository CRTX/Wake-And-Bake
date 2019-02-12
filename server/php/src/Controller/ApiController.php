<?php
namespace App\Controller;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

class ApiController extends Controller
{
    /**
     * @Route("/api/wake/{macAddress}/{interface}")
     */
    public function wake($macAddress, $interface = 'eth0')
    {
        $process = new Process(['etherwake', '-i', $interface, $macAddress]);
        $process->run();

        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        return new Response($process->getOutput());
    }
}
