<?php

require_once __DIR__  . '/../vendor/autoload.php';

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

foreach ($asakeFunctions as $function) {
    $application->add(new \Asake\Console\ReflectionFunctionCommand($function));
}

return $application->run();
