<?php

require_once __DIR__  . '/../vendor/autoload.php';

\Doctrine\Common\Annotations\AnnotationRegistry::registerLoader('class_exists');

$directory = getcwd();
$definedFunctions = get_defined_functions();

while (null !== $directory) {
    $file = $directory . DIRECTORY_SEPARATOR . 'asake.php';

    if (file_exists($file)) {
        require_once $file;

        break;
    }

    $newDirectory = dirname($directory);

    if ($newDirectory === $directory) {
        throw new Error("No file found");
    }

    $directory = $newDirectory;
}

$application = new \Symfony\Component\Console\Application();
$asakeFunctions = array_diff(get_defined_functions()['user'], $definedFunctions['user']);
$container = \DI\ContainerBuilder::buildDevContainer();
$envVars = getenv();
$context = new Asake\Context\Context($container, $directory, $envVars);
$reader = new \Asake\Annotation\FunctionAnnotationReader();

foreach ($asakeFunctions as $function) {
    $reflectionFunction = new \ReflectionFunction($function);
    $task = $reader->getFunctionAnnotation($reflectionFunction, \Asake\Annotation\Task::class);

    if (null === $task) {
        continue;
    }

    $application->add(new \Asake\Console\ReflectionFunctionCommand($context, $reflectionFunction, $task));
}

return $application->run();
