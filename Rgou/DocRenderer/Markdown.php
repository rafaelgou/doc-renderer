<?php
namespace Rgou\DocRenderer;

use Michelf\Markdown as MarkdownParser;
use Michelf\MarkdownExtra as MarkdownExtraParser;


/**
 * Markdown Renderer
 * 
 * @package  DocRenderer
 * @author   Rafael Goulart <rafaelgou@gmail.com>
 */
class Markdown extends AbstractRenderer
{

    /**
     * Render the HTML
     * 
     * @param string  $input              Source input to render
     * @param integer $initialHeaderLevel Level to render
     * 
     * @return string 
     */
    public function render($input, $initialHeaderLevel = 1)
    {
        $this->parse($input, $initialHeaderLevel);

        return $this->renderTwig();
    }

    /**
     * Render the HTML
     * 
     * @param string  $input              Source input to render
     * @param integer $initialHeaderLevel Level to render
     * 
     * @return string 
     */
    protected function parse($input, $initialHeaderLevel = 1) 
    {

        // Markdown file to read
        $mdFileName       = $input;
        $this->config['mdSource'] = file_get_contents($mdFileName);

        // Rendering Markdown
        $parser = new MarkdownExtraParser();
        $html   = $parser->transform($this->config['mdSource']);

        // Getting TOC and doctitle
        if (array_key_exists('use_toc', $this->config) && $this->config['use_toc']) {
            $this->config['htmlData'] = $this->getTableOfContents($html);
        } else {
            $this->config['htmlData'] = $parser->transformMarkdown($this->config['mdSource']);
        }
        $this->config['doctitle'] = $this->getTitle($html);

        return $this->config['htmlData'];
    }

}
