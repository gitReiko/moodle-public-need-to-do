<?php 

namespace NTD\Classes\Components\Messanger\Renderer;

/**
 * Returns my work part of block related to messanger.
 */
class MyWork 
{
    /**
     * Data necessary for rendering
     */
    //private $data;

    /**
     * Prepares data for class.
     */
    function __construct()
    {
        //$this->prepare_data_for_rendering();
    }

    /**
     * Returns my work part of block content related to messanger.
     * 
     * @return string my work part of block
     */
    public function get_messanger_part() : string 
    {
        $msgr = $this->get_messanger_header();
        //$msgr.= $this->get_my_unread_messages();
        
        return $msgr;
    }

    /**
     * Returns messanger header. 
     * 
     * @return string messanger header
     */
    private function get_messanger_header() : string 
    {
        $attr = array('class' => 'ntd-messanger-header');
        $text = get_string('messages_not_read_in_chat', 'block_needtodo');
        return \html_writer::tag('p', $text, $attr);
    }


}
