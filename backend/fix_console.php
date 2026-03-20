<?php
require 'vendor/autoload.php';
use App\Kernel;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArgvInput;

$input = new ArgvInput();
$env = $input->getParameterOption(['--env', '-e'], 'prod');
$debug = (bool) $input->getParameterOption(['--no-debug'], false) ? false : true;

$kernel = new Kernel($env, $debug);
$application = new Application($kernel);
$application->run($input);
