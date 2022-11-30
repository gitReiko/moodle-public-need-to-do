<?php 

namespace NTD\Classes\Renderer;

require_once __DIR__.'/../components/messanger/renderer/my_work.php';

/**
 * Forms my work part of the block.
 */
class MyWork
{

    /**
     * Returns my work part of the block.
     * 
     * @return string my work in html format.
     */
    public function get_my_work() : string 
    {
        $myWork = $this->get_my_works_header();
        $myWork.= $this->get_messanger_part();

        return $myWork;
    }

    /**
     * Returns my work header.
     * 
     * @return string my work header
     */
    private function get_my_works_header() : string 
    {
        $attr = array('class' => 'ntd-my-work-header');
        $text = get_string('my_work', 'block_needtodo');
        return \html_writer::tag('p', $text, $attr);
    }

    /**
     * Returns my work part of block related to messanger.
     * 
     * @return string my work part of block related to messanger
     */
    private function get_messanger_part() : string 
    {
        $renderer = new \NTD\Classes\Components\Messanger\Renderer\MyWork;
        return $renderer->get_messanger_part();
    }

}
