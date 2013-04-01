<?php
require_once('../vendor/autoload.php');

$data = array();

// Loading config
$yaml               = new \Symfony\Component\Yaml\Parser();
$configFile         = file_get_contents(__DIR__ . '/basic.yml');
$data               = $yaml->parse($configFile);

// Loading Twig
$loader = new Twig_Loader_Filesystem(__DIR__ . '/../Resources/views');
$twig   = new Twig_Environment($loader);

// Optional - just for showing sources for your information

$data['mdFileName']  = 'basic.md';
$data['mdSource']    = file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . $data['mdFileName']);

$data['phpFileName'] = ('basic.php');
$data['phpSource']   = highlight_file (__FILE__, true);

$data['configFile'] = $configFile;

// Extension Twig Markdown
$parser = new \dflydev\markdown\MarkdownParser();
$twig->addExtension(new \Aptoma\Twig\Extension\MarkdownExtension($parser));

// Rendering Twig
$template = $twig->loadTemplate('examples/basic.html.twig');
$template->display($data);


