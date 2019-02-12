<?php

declare(strict_types=1);

namespace Asake\Context;

use Amp\Process\Process;
use Psr\Container\ContainerInterface;

class Context implements ContainerInterface
{
    private $container;

    private $workingDir;

    private $envVars;

    public function __construct(ContainerInterface $container, string $workingDir, array $envVars = [])
    {
        $this->container = $container;
        $this->workingDir = $workingDir;
        $this->envVars = $envVars;
    }

    public function run($command)
    {
        $process = new Process($command, $this->workingDir, $this->envVars);
        $process->start();

        return $process;
    }

    public function withEnv(array $envVars): self
    {
        return new Context($this->container, $this->workingDir, array_merge($this->envVars, $envVars));
    }

    public function within(string $directory): self
    {
        return new Context($this->container, $this->workingDir . $directory, $this->envVars);
    }

    /**
     * {@inheritdoc}
     */
    public function get($id)
    {
        return $this->container->get($id);
    }

    /**
     * {@inheritdoc}
     */
    public function has($id)
    {
        return $this->container->has($id);
    }
}
