<?php
namespace Rgou\DocRenderer;

/**
 * RestructuredText Renderer
 * 
 * @package  DocRenderer
 * @author   Rafael Goulart <rafaelgou@gmail.com>
 */
class RestructuredText extends AbstractRenderer
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
     * @param string  $rst2htmlPath       Restructured Text executable
     * 
     * @return string 
     */
    protected function parse($input, $initialHeaderLevel = 1, $rst2htmlPath = '/usr/local/bin/rst2html.py') 
    {

        $rst2html = "{$rst2htmlPath} --stylesheet-path='' " .
            "--initial-header-level={$initialHeaderLevel} --no-doc-title " .
            "--no-file-insertion --no-raw ";
        $html = shell_exec($rst2html . $input); 
        $html = preg_replace('/.*<body>\n(.*)<\/body>.*/s', '${1}', $html);
        $this->config['htmlData'] = $this->getTableOfContents($html);
        $this->config['doctitle'] = $this->getTitle($html);

        return $this->config['htmlData'];
    }
}
