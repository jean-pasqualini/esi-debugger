<?php
require 'vendor/autoload.php';

try {
    $configurationProcessor = new AppConfigurationProcessor();
    $configurationLoader = new ConfigurationLoader(__DIR__);
    $configuration = $configurationProcessor->process(
        $configurationLoader->load('config.yml')
    );
} catch (\Exception $e) {
    echo $e->getMessage();
    exit(1);
}

echo 'configuration chargée : '.PHP_EOL;
echo print_r($configuration, true);
echo PHP_EOL;