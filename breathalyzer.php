<?php
require_once 'vendor/autoload.php';

use \Symfony\Component\Console\Application;

$application = new Application();
$command = new Breathalyze\CalculateLevenshteinCommand();
$application->add($command);
$application->setDefaultCommand($command->getName(), true);
$application->run();