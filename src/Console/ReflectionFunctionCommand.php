<?php

namespace Asake\Console;

use Asake\Annotation\Task;
use Asake\Context\Context;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ReflectionFunctionCommand extends Command
{
    private $function;

    private $startingContext;

    private $task;

    public function __construct(Context $startingContext, \ReflectionFunction $function, Task $task)
    {
        $this->function = $function;
        $this->startingContext = $startingContext;
        $this->task = $task;

        parent::__construct(str_replace('\\',':', strtolower($this->function->getName())));
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $parameters = $this->function->getParameters();
        $this->setDescription($this->task->description);

        foreach ($parameters as $parameter) {
            if ($parameter->getPosition() === 0 && $parameter->hasType() && $parameter->getType()->getName() === Context::class) {
                continue;
            }

            if (!$parameter->isOptional()) {
                if ($parameter->hasType() && $parameter->getType()->getName() === 'bool') {
                    $this->addOption(
                        $parameter->getName(),
                        null,
                        InputOption::VALUE_NONE,
                        ''
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
                        ''
                    );
                } elseif ($parameter->isVariadic()) {
                    $this->addArgument(
                        $parameter->getName(),
                        InputArgument::IS_ARRAY | InputArgument::OPTIONAL,
                        ''
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
            if ($parameter->getPosition() === 0 && $parameter->hasType() && $parameter->getType()->getName() === Context::class) {
                $values[] = $this->startingContext;

                continue;
            }

            if (!$parameter->isOptional() && (!$parameter->hasType() || $parameter->getType()->getName() !== 'bool')) {
                $values[] = $input->getArgument($parameter->getName());
            } elseif ($parameter->isVariadic()) {
                $values = array_merge($values, $input->getArgument($parameter->getName()));
            } else {
                $values[] = $input->getOption($parameter->getName());
            }
        }

        return $this->function->invoke(...$values);
    }
}