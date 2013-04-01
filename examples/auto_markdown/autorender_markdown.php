<?php
/**
 * Autorenderer
 */

// Path to DocRenderer
$docRendererPath = __DIR__ . '/../../';
require_once($docRendererPath . '/vendor/autoload.php');

use \Rgou\DocRenderer\Markdown as MdRenderer;


// Loading config
$configFile = __DIR__ . '/autorender.yml';
if (file_exists($configFile)) {
    $yaml = new \Symfony\Component\Yaml\Parser();
    $config = $yaml->parse(file_get_contents($configFile));
} else {
    $config = array();
}

$mdRenderer = new MdRenderer($config);
$config     = $mdRenderer->getConfig();

// Checking text version    
$requested     = rawurldecode($config['requestUri']);
$requestParts  = explode( '-', $requested );
if (array_pop($requestParts) == $config['text_suffix']) {
    // replace the requested name with extension removed
    $requested = implode( '-', $requestParts );
    header( "Content-type: text/plain;charset=utf-8" );
    readfile( $_SERVER['DOCUMENT_ROOT'] . $requested );
    exit;
}

echo $mdRenderer->render($_SERVER['DOCUMENT_ROOT'] . $requested);

