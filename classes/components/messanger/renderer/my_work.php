<?php 

namespace NTD\Classes\Components\Messanger\Renderer;

require_once __DIR__.'/lib.php';

use NTD\Classes\Lib\Enums as Enums;

/**
 * Returns my work part of block related to messanger.
 */
class MyWork 
{
    /**
     * Block instance params.
     */
    private $params;

    /**
     * Data necessary for rendering
     */
    private $data;

    /**
     * Prepares data for class.
     */
    function __construct(\stdClass $params)
    {
        $this->params = $params;
        $this->prepare_data_for_rendering();
    }

    /**
     * Returns my work part of block content related to messanger.
     * 
     * @return string my work part of block
     */
    public function get_messanger_part() : string 
    {
        $msgr = '';

        if(!empty($this->data))
        {
            $msgr = Lib::get_messanger_header();
            $msgr.= $this->get_my_unread_messages();
        }
        
        return $msgr;
    }

    /**
     * Prepares data necessary for rendering.
     */
    private function prepare_data_for_rendering() : void 
    {
        global $DB, $USER;

        $where = array(
            'component' => Enums::MESSANGER,
            'teacherid' => $USER->id
        );

        $this->data = json_decode($DB->get_field('block_needtodo', 'info', $where));
    }

    /**
     * Returns messages unread by the teacher.
     * 
     * @return string unread messages
     */
    private function get_my_unread_messages() : string 
    {
        $list = Lib::get_teacher_line(
            $this->data, 
            $this->params->instance,
            Enums::MY_WORK
        );

        $linkToChat = true;
        $list.= Lib::get_unreaded_from_lines(
            $this->data, 
            $this->params->instance, 
            Enums::MY_WORK,
            $linkToChat
        );

        return $list;
    }


}
