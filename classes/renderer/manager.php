<?php 

namespace NTD\Classes\Renderer;

require_once __DIR__.'/../components/messanger/renderer/manager.php';

/**
 * Forms part of the block for the manager.
 */
class Manager 
{

    /**
     * Block instance config.
     */
    private $config;

    /**
     * Prepares data.
     */
    function __construct(\stdClass $config)
    {
        $this->config = $config;
    }

    /**
     * Returns part of the block Need to do for the manager.
     * 
     * @return string part of the block Need to do for the manager in html format.
     */
    public function get_manager_part() : string 
    {
        $manager = $this->get_new_line();
        $manager.= $this->get_manager_header();
        $manager.= $this->get_messanger_part();

        return $manager;
    }

    /**
     * Returns html new line.
     * 
     * @return string html new line
     */
    private function get_new_line() : string 
    {
        return \html_writer::empty_tag('br');
    }

    /**
     * Returns header of manager part.
     * 
     * @return string header of manager part
     */
    private function get_manager_header() : string 
    {
        $text = get_string('info_about_other_users', 'block_needtodo');
        $text = \html_writer::tag('b', $text);
        $text = \html_writer::tag('p', $text);
        return $text;
    }

    /**
     * Returns manager part of block content related to messanger.
     * 
     * @return string manager part of block content related to messanger
     */
    private function get_messanger_part() : string 
    {
        $renderer = new \NTD\Classes\Components\Messanger\Renderer\Manager($this->config);
        return $renderer->get_messanger_part();
    }

}
