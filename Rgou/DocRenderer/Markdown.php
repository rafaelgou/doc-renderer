<?php
namespace Rgou\DocRenderer;

class Markdown extends AbstractRenderer
{

    public function render($input, $initialHeaderLevel = 1)
    {
        $this->parse($input, $initialHeaderLevel);

        return $this->renderTwig();
    }

    protected function parse($input, $initialHeaderLevel = 1, $rst2htmlPath = '/usr/bin/rst2html') 
    {

        // Markdown file to read
        $mdFileName       = $input;
        $this->config['mdSource'] = file_get_contents($mdFileName);

        // Rendering Markdown
        $parser = new \dflydev\markdown\MarkdownExtraParser();
        $html   = $parser->transformMarkdown($this->config['mdSource']);

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
