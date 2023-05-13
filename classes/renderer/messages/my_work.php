<?php 

namespace NTD\Classes\Renderer\Messages;

require_once 'main.php';

use \NTD\Classes\Lib\Enums as Enums;
use \NTD\Classes\Lib\Common as cLib;

/**
 * Forms messanger part of block for my work.
 */
class MyWork extends Main 
{

    /**
     * Prepares data for class.
     */
    function __construct(\stdClass $params)
    {
        parent::__construct($params);
    }

    /**
     * Prepares data necessary for rendering.
     */
    protected function prepare_data_for_rendering() : void 
    {
        global $DB, $USER;

        $where = array(
            'component' => Enums::MESSANGER,
            'entityid' => $USER->id
        );

        $this->data = json_decode($DB->get_field('block_needtodo', 'info', $where));
    }

    /**
     * Returns messages unread by the teacher.
     * 
     * @return string unread messages
     */
    protected function get_my_unread_messages() : string 
    {
        return $this->get_from_users_unread_messages();
    }

    /**
     * Returns from users unread messages.
     * 
     * @return string unreaded lines
     */
    private function get_from_users_unread_messages() : string 
    {
        $messages = '';

        $i = 0;
        foreach($this->data->unreadedMessages->fromUsers as $fromUser)
        {
            $class = $this->get_message_class();
            $tooMuchClass = $this->get_hidden_elements_class_for_more_button();

            if(cLib::is_item_number_too_large($i))
            {
                $class.= ' ntd-hidden-box '.$tooMuchClass;
            }

            $attr = array(
                'class' => $class,
                'onclick' => 'window.location.replace("/message/index.php");', // redirect to link 
                'title' => $this->get_student_title($fromUser)
            );

            $text = $fromUser->name.$this->get_unread_count($fromUser->count);
            $messages.= \html_writer::tag('div', $text, $attr);

            $i++;
        }

        if(cLib::is_item_number_too_large($i)) 
        {
            $messages.= cLib::get_show_more_button($tooMuchClass);
        }

        return $messages;
    }

    /**
     * Returns message class. 
     * 
     * @return string class 
     */
    private function get_message_class() : string 
    {
        return 'ntd-level-1 ntd-cursor-pointer ntd-tooltip ';
    }

    /**
     * Returns class of hidden elements for more button. 
     * 
     * @return string id of more button
     */
    private function get_hidden_elements_class_for_more_button() : string 
    {
        return Enums::MORE.Enums::MY_WORK.Enums::MESSAGES.$this->params->instance;
    }
    
}
