<?php 

namespace NTD\Classes\Renderer;

/**
 * Forms part of the block for the manager.
 */
class Manager 
{

    /**
     * Returns part of the block Need to do for the manager.
     * 
     * @return string part of the block Need to do for the manager in html format.
     */
    public function get_manager_part() : string 
    {
        $manager = $this->get_new_line();
        $manager.= $this->get_manager_header();

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
        return $text;
    }

}
