<?php 
//header('Location: index.md'); 

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

// Definning defaults
$config = array(
    'menu_title' => array_key_exists('title', $config) ? $config['title'] : 'DocRenderer',
    'base_path'  => $config['base_path'] ? $config['base_path'] : __DIR__,
);

// Searching directories and files to create index
$dirs = MdRenderer::dirToArray($config['base_path']);

$html = array();
foreach ($dirs as $key => $value) {
    if ($htmlTmp = MdRenderer::renderDir($value, 2, 'both', $key)) {
        $html[] = $htmlTmp;
    }
}
$html = '<ul>' . implode(PHP_EOL, $html) . '</ul>';

// Setting title
$config['doctitle'] = 'DocRenderer Index';
$config['htmlData'] = $html;

// Loading and Rendering Twig
$loader = new Twig_Loader_Filesystem($docRendererPath . '/Resources/views'); 
$twig   = new Twig_Environment($loader);
$template = $twig->loadTemplate('autorenderer.html.twig');
$template->display($config);

