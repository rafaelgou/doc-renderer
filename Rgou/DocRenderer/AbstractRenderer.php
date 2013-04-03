<?php
namespace Rgou\DocRenderer;

/**
 * AbstractRenderer
 * 
 * Base class for renderers
 *
 * @package  DocRenderer
 * @author   Rafael Goulart <rafaelgou@gmail.com>
 */
abstract class AbstractRenderer
{

    /**
     * @static
     * @var array
     */
    static public $extensions    = array('markdown', 'mdown', 'md', 'mkd', 'rst');

    /**
     * @static
     * @var array
     */
    static public $mdExtensions  = array('markdown', 'mdown', 'md', 'mkd');

    /**
     * @static
     * @var array
     */
    static public $rstExtensions = array('rst');

    protected $template;

    protected $html;

    protected $config = array();

    /**
     * Render the HTML
     * 
     * @param string  $input              Source input to render
     * @param integer $initialHeaderLevel Level to render
     * 
     * @return string 
     */
    abstract public function render($input, $initialHeaderLevel = 1);

    /**
     * Constructor
     * 
     * @param array $config The configuration
     * 
     * @return void
     */
    public function __construct(array $config)
    {
        $this->setConfig($config);
    }

    /**
     * Set the config
     * 
     * @param array $config The Config
     */
    public function setConfig(array $config)
    {
        $defaults = array(
            'title'          => 'DocRenderer',
            'menu_title'     => 'DocRenderer',
            'base_path'      => '/var/www/docs',
            'base_url'       => 'http://localhost/docs',
            'text_suffix'    => 'text',
            'permalink'      => '<i class="icon-link"></i>',
            'use_toc'        => true,
            'link_to_source' => true,
            'template'       => 'autorenderer.html.twig',
            'template_path'  => __DIR__ . '/../../Resources/views',
            'requestUri'     => $_SERVER['REQUEST_URI'],
            'requestUriText' => "{$_SERVER['REQUEST_URI']}-%text_suffix%",
        );

        foreach ($defaults as $key => $value) {
            $this->config[$key] = array_key_exists($key, $config) ? $config[$key] : $value;
        }
        $this->config['requestUriText'] = str_replace('%text_suffix%', $this->config['text_suffix'], $this->config['requestUriText']);
    }

    /**
     * Set the template path
     * 
     * @param string $templatePath The Template Path
     * 
     * @return \Rgou\DocRenderer\AbstractRenderer 
     */
    public function setTemplatePath($templatePath)
    {
        $this->config['template_path'] = (string) $templatePath;

        return $this;
    }
    
    /**
     * Set the template
     * 
     * @param string $template The Template
     * 
     * @return \Rgou\DocRenderer\AbstractRenderer 
     */
    public function setTemplate($template)
    {
        $this->config['template'] = (string) $template;

        return $this;
    }

    /**
     * Get the config
     * 
     * @return array
     */
    public function getConfig()
    {
        return $this->config;
    }
    
    /**
     * Get HTML with from TOC
     *
     * @param $html string The HTML
     *
     * @return string
     */
    public function getTableOfContents($html)
    {
        preg_match_all("/(<h([1-6]{1})[^<>]*>)(.+)(<\/h[1-6]{1}>)/", $html, $matches, PREG_SET_ORDER);

        $toc = "";
        $list_index = 0;
        $indent_level = 0;
        $raw_indent_level = 0;
        $anchor_history = array();

        foreach ($matches as $val) {

            ++$list_index;
            $prev_indent_level = $indent_level;
            $indent_level = $val[2];
            $anchor = self::getSafeParameter( $val[3] );

            // ensure that we don't reuse an anchor
            $anchor_index = 1;
            $raw_anchor = $anchor;
            while ( in_array( $anchor, $anchor_history ) ) {
                $anchor_index++;
                $anchor = $raw_anchor . strval( $anchor_index );
            }

            array_push( $anchor_history, $anchor );
            if ( $indent_level > $prev_indent_level ) {
                // indent further (by starting a sub-list)
                $toc .= "\n<ul>\n";
                $raw_indent_level++;
            }
            if ( $indent_level < $prev_indent_level ) {
                // end the list item
                $toc .= "</li>\n";
                // end this list
                $toc .= "</ul>\n";
                $raw_indent_level--;
            }
            if ( $indent_level <= $prev_indent_level ) {
                // end the list item too
                $toc .= "</li>\n";
            }
            // add permalink?
            if ( $this->config['permalink'] != "" ) {
                $pl = ' <a href="#'.$anchor.'" class="permalink" title="link to this section">' . $this->config['permalink'] . '</a>';
            } else {
                $pl = "";
            }
            // print this list item
            $toc .= '<li><a href="#'.$anchor.'">'. $val[3] . '</a>';
            $Sections[$list_index] = '/' . addcslashes($val[1] . $val[3] . $val[4], '/.*?+^$[]\\|{}-()') . '/'; // Original heading to be Replaced
            $SectionWIDs[$list_index] = '<h' . $val[2] . ' id="'.$anchor.'">' . $val[3] . $pl . $val[4]; // New Heading
        }
        // close out the list
        $toc .= "</li>\n";
        for ( $i = $raw_indent_level; $i > 1; $i-- ) {
            $toc .= "</ul>\n</li>\n";
        }
        $toc .= "</ul>\n";

        return '<div id="toc" class="well span3 pull-right">' . $toc . '</div>' . "\n" . preg_replace($Sections, $SectionWIDs, $html, 1);
    }

    /**
     * Get Title from HTML
     *
     * Parses HTML and find first title (h1  to h6)
     * 
     * @param $html string The HTML
     *
     * @return string
     */
    public function getTitle($html)
    {
        if ( preg_match( "/<h[1-6]{1}[^<>]*>(.+)<\/h[1-6]{1}>/", $html, $matches ) ) {

            return strip_tags( $matches[1] );

        } else {

            return "Untitled Markdown Document";

        }
    }

    /**
     * Get Safe Parameter (slug)
     *
     * Change a string into something that can be safely used as a parameter
     * in a URL. Example: "Rob is a PHP Genius" would become "rob_is_a_php_genius" 
     * 
     * @static
     * 
     * @param $unsafe string Unsafe text
     *
     * @return string
     */
    static public function getSafeParameter($unsafe) 
    {
        // remove HTML tags
        $unsafe = strip_tags($unsafe);

        // remove all but alphanumerics, spaces and underscores
        $lowAN = preg_replace("/[^a-z0-9_ ]/", "", strtolower($unsafe));

        // replace spaces/underscores with underscores
        $clean = preg_replace( "/[ _]+/", "_", $lowAN );

        // remove any leading or trailing underscores
        $safe = trim($clean, '_');

        return $safe;
    }

    /**
     * Get an array with directory content
     * 
     * @static
     * 
     * @param string $dir The directory to scan
     * 
     * @return array
     */
    static public function dirToArray($dir)
    {
        $result = array();
        $cdir = scandir($dir);
        foreach ($cdir as $key => $value) {
            if (!in_array($value, array('.', '..', '.git', 'nbproject', 'DocDevel'))) {
                if (is_dir($dir . DIRECTORY_SEPARATOR . $value)) {
                    $result[$value] = self::dirToArray($dir . DIRECTORY_SEPARATOR . $value);
                } else {
                    $result[] = $value;
                }
            }
        }
        return $result;
    }

    /**
     * Render a file html string
     *
     * @param string  $file  The filename
     * @param integer $level Level to render
     * @param string  $type  Type: both, markdown or rst
     * 
     * @return mixed
     */
    static public function renderFile($file, $level, $type = 'both')
    {
        switch($type) {
            case 'markdown';
            case 'md';
            case 'mkd';
                $extensions = static::$mdExtensions;
                break;
            
            case 'rst';
                $extensions = static::$rstExtensions;
                break;

            default:
            case 'both';
                $extensions = static::$extensions;
                break;
        }
        
        $tmpFile = explode('.', $file);
        $ext     = array_pop($tmpFile);
        if (in_array($ext, $extensions)) {
            $filename = explode(DIRECTORY_SEPARATOR, $file);
            $filename = array_pop($filename);
            return "<li><a href=\"{$file}\">{$filename}</a></li>" . PHP_EOL;
        } else {
            return false;
        }
    }

    /**
     * Render a directory html string
     *
     * @param string  $dir     The directory
     * @param integer $level   Level to render
     * @param string  $type    Type: both, markdown or rst
     * @param string  $baseDir Base directory
     * 
     * @return string
     */
    static public function renderDir($dir, $level, $type = 'both', $baseDir = '')
    {
        $html = array();

        if (!is_array($dir)) {
            return self::renderFile($dir, $level, $type);
        } else {

            foreach($dir as $key => $value) {

                $baseDirName = explode(DIRECTORY_SEPARATOR, $baseDir);
                $baseDirName = array_pop($baseDirName);
                $header = "<li><strong>{$baseDirName}</strong><ul>" . PHP_EOL;
                if (is_array($value)) {
                    if ($dirHtml = self::renderDir($value, $level +  1, $type, $baseDir . DIRECTORY_SEPARATOR . $key)) {
                        $html[] = $dirHtml;
                    }
                } else {
                    if ($fileHtml = self::renderFile($baseDir . DIRECTORY_SEPARATOR  . $value, $level, $type)) {
                        $html[] = $fileHtml;
                    }
                }
                $footer = "</ul></li>" . PHP_EOL;
            }

            if (count($html)) {
                $html = $header . implode(PHP_EOL, $html) . $footer;
            } else {
                $html = implode(PHP_EOL, $html);
            }
            return $html;
        }
    }

    /**
     * Render HTML from Twig template
     * 
     * @param mixed $config       The config array or false for default
     * @param mixed $template     The template string or false for default
     * @param mixed $templatePath The base template path or false for default
     * 
     * @return string
     */
    public function renderTwig($config = false, $template = false, $templatePath = false)
    {
        $template     = $template ? $template : $this->config['template'];
        $templatePath = $templatePath ? $templatePath : $this->config['template_path'];
        $config       = $config ? $config : $this->config;

        // Loading and Rendering Twig
        $loader   = new \Twig_Loader_Filesystem($templatePath); 
        $twig     = new \Twig_Environment($loader);
        $template = $twig->loadTemplate($template);

        return $template->render($config);
    }

}
