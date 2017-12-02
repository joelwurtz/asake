<?php

namespace Asake\Console;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ReflectionFunctionCommand extends Command
{
    private $function;

    public function __construct($function)
    {
        $this->function = new \ReflectionFunction($function);

        parent::__construct(str_replace('\\',':', strtolower($this->function->getName())));
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $parameters = $this->function->getParameters();

        foreach ($parameters as $parameter) {
            if (!$parameter->isOptional()) {
                if ($parameter->hasType() && $parameter->getType()->getName() === 'bool') {
                    $this->addOption(
                        $parameter->getName(),
                        null,
                        InputOption::VALUE_NONE,
                        '',
                        true
                    );
                } else {
                    $this->addArgument(
                        $parameter->getName(),
                        $parameter->isArray() ? InputArgument::IS_ARRAY | InputArgument::REQUIRED : InputArgument::REQUIRED,
                        ''
                    );
                }
            }

            if ($parameter->isOptional()) {
                if ($parameter->hasType() && $parameter->getType()->getName() === 'bool') {
                    $this->addOption(
                        $parameter->getName(),
                        null,
                        InputOption::VALUE_NONE,
                        '',
                        $parameter->getDefaultValue()
                    );
                } else {
                    $this->addOption(
                        $parameter->getName(),
                        null,
                        $parameter->isArray() ? InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY : InputOption::VALUE_OPTIONAL,
                        '',
                        $parameter->getDefaultValue()
                    );
                }
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $values = [];
        $parameters = $this->function->getParameters();

        foreach ($parameters as $parameter) {
            if (!$parameter->isOptional() && (!$parameter->hasType() || $parameter->getType()->getName() !== 'bool')) {
                $values[] = $input->getArgument($parameter->getName());
            } else {
                $values[] = $input->getOption($parameter->getName());
            }
        }

        return $this->function->invokeArgs($values);
    }
}