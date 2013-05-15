Doc Renderer
============
Author: Rafael Goulart <rafaelgou@gmail.com>

Doc Renderer is a tool to render [Markdown](http://daringfireball.net/projects/markdown/)
and [RestructuredText](http://docutils.sourceforge.net/rst.html) in PHP.

It uses [Twitter Bootstrap](http://twitter.github.com/bootstrap/) as CSS Framework, 
[Twig](http://twig.sensiolabs.org/) as template engine.

[Google Prettify](http://google-code-prettify.googlecode.com/svn/trunk/README.html) is used for syntax highlight.

DocRenderer is inspired by [RenderMarkdown](https://github.com/skurfer/RenderMarkdown),
from which some functions were borrewed, thanks to [Rob McBroom](https://github.com/skurfer).

Screenshots
-----------

![renderer](https://raw.github.com/rafaelgou/doc-renderer/master/images/screenshot-renderer.png "The Renderer")

![index](https://raw.github.com/rafaelgou/doc-renderer/master/images/screenshot-index.png "The Index")

Install
-------

Composer:

    {
        "require": {
            // ...
            "rafaelgou/doc-renderer": "*"
        }
    }

Or clonning:

    git clone git://github.com/rafaelgou/doc-renderer.git


Or just downloading lastest version:

    wget https://github.com/rafaelgou/doc-renderer/archive/master.zip

For Restructured Text, Docutils is needed:

    apt-get install python-docutils


Introduction
------------

The magic is:

- Some Markdown or Restructured Text
- For Markdown, [PHP-Markdown](https://github.com/michelf/php-markdown)
- For Restructured Text, [Docutils](http://docutils.sourceforge.net/rst.html)
- For rendering the HTML stuff, [Twig](http://twig.sensiolabs.org/)
- For a pretty good face, [Twitter Bootstrap](http://twitter.github.com/bootstrap/)
  (Bootstrap and JQuery are loaded from CDN sources)
- For syntax highlighting, [Google Prettify](http://google-code-prettify.googlecode.com/svn/trunk/README.html)
- For an easy configuration, [sfYaml](http://symfony.com/doc/current/components/yaml/introduction.html)

[Composer](getcomposer.org/) lets everything easier to glue togheter, and a basic
example is just include the Composer autoloader, loads a configuration and
uses the libraries togheter:


    <?php
    // File examples/basic.php
    
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

The above file uses this sample YML file:

    # File examples/basic.yml
    title: Basic

loads this Markdown file:

    # examples/basic.md
    #Basic Sample

    Paragraph of text.

and renders with this twig template:

    <!-- File Resources/views/examples/basic.html.twig -->
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="utf-8">
        <title>Markdown Renderer - {{ title }}</title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta name="description" content="">
        <meta name="author" content="">

        <!-- Le styles -->
        <link href="//netdna.bootstrapcdn.com/twitter-bootstrap/2.3.1/css/bootstrap-combined.min.css" rel="stylesheet">
        <style>
            body { padding-top: 60px; /* 60px to make the container go all the way to the bottom of the topbar */ }
            .footer { text-align: center; padding: 20px 0; margin-top: 40px; border-top: 1px solid #e5e5e5; background-color: #f5f5f5; }
            .footer p { margin-bottom: 0; color: #777; }
        </style>
        <link href="//netdna.bootstrapcdn.com/twitter-bootstrap/2.3.1/css/bootstrap-responsive.min.css" rel="stylesheet">

    </head>

    <body>

        <div class="navbar navbar-inverse navbar-fixed-top">
        <div class="navbar-inner">
            <div class="container">
            <button type="button" class="btn btn-navbar" data-toggle="collapse" data-target=".nav-collapse">
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>
            <a class="brand" href="#">{{ title }}</a>
            <div class="nav-collapse collapse">
                <ul class="nav">
                <li class="active"><a href="index.php">Index of Examples</a></li>
                <li><a href="">About Markdown Basic</a></li>
                </ul>
            </div><!--/.nav-collapse -->
            </div>
        </div>
        </div>

        <div class="container">

    {% markdown %}{{ mdSource }}{% endmarkdown %}

    <hr />

    <h4>Markdown Source "{{ mdFileName }}"</h4>

    <pre>
    {{ mdSource }}
    </pre>

    <hr />

    <h4>PHP Source "{{ phpFileName }}"</h4>

    {{ phpSource | raw }}

        </div> <!-- /container -->

        <footer class="footer">
            <div class="container">
                <p><a href="http://github.com/rafaelgou/markdown-renderer" target="_blank">Markdown Renderer</a> &copy; <a href="http://tech.rgou.net" target="_blank">Rafael Goulart</a> 2013</p>
            </div>                
        </footer>

        <!-- Javascript CDN -->
        <script src="http://code.jquery.com/jquery-1.9.1.min.js"></script>
        <script src="//netdna.bootstrapcdn.com/twitter-bootstrap/2.3.1/js/bootstrap.min.js"></script>

    </body>
    </html>

As you can see, is just everything glued together. But there's something more interesting below.


Rendering a full directory
--------------------------

On the examples directory, there are three directories:

- auto (for both markdow and rst files)
- auto_markdown
- auto_rst

Any of them can transform a directory with `.md` or `.rst` into a
browseable directory of docs.

How to use:

- Copy any of these directories to a Apache+PHP directory, renaming if you want to;

- Point both PHP files to the right doc-renderer directory:
  `$docRendererPath = __DIR__ . '/../doc-renderer'; // Change here!`

- Be sure the Apache directive `AllowOverride All` is set for this directory!

- Put some Markdown or Restructured Text on this folder (even subdirectories)

- Open in the browser

The `index.php` searchs for all related files (see `.htaccess` file for more info)
and `autorender*.php` do the magic.

Markdown
--------

DocRenderer uses [PHP-Markdown-Extra](http://michelf.ca/projects/php-markdown/extra/) by default.

This makes easier to use syntax highlighting as you can pass the language you want:

    ~~~ .php
    <?php 
        echo 'foo';
        $var = array('test' => 123);
        print_r($var);
    ?>
    ~~~

Customizing the template
------------------------

*Twitter Bootstrap*

Change in the `<head>`

    <link href="//netdna.bootstrapcdn.com/twitter-bootstrap/2.3.1/css/bootstrap-combined.min.css" rel="stylesheet">

to whatever other theme you want. See [Bootstrap CDN](http://www.bootstrapcdn.com/) for more free options. 

*Google Prettify*:

You can add languages not supported by default adding lines just like bellow on bottom of the page
(just before `</body>`):

    <script src="https://google-code-prettify.googlecode.com/svn/loader/run_prettify.js"></script>
    <script src="https://google-code-prettify.googlecode.com/svn/loader/lang-yaml.js"></script>

Additional languages are list [here](https://google-code-prettify.googlecode.com/svn/loader/).

You can also change the skin on `<head>`

    <link href="//google-code-prettify.googlecode.com/svn/loader/skins/desert.css" rel="stylesheet">

See available skins [here](https://google-code-prettify.googlecode.com/svn/loader/skins/).


