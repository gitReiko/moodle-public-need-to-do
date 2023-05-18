<?php 

namespace NTD\Classes\Renderer\Messages;

require_once 'main.php';

use \NTD\Classes\Lib\Getters\Teachers as tGet;
use \NTD\Classes\Lib\Enums as Enums;
use \NTD\Classes\Lib\Common as cLib;

/**
 * Forms messanger part of block for manager.
 */
class Manager extends Main 
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
        $needtodo = $this->get_messanger_needtodo_data();

        $data = array();

        foreach($needtodo as $value)
        {
            $data[] = json_decode($value->info);
        }

        $this->data = $data;
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
     * Returns data related to messanger from database.
     * 
     * @return array if data exists
     * @return null if not
     */
    private function get_messanger_needtodo_data() 
    {
        global $DB;

        $teachers = $this->get_teachers();
        $teachersInCondition = tGet::get_where_in_condition_from_teachers_array($teachers);

        // Teachers may not exist
        if($teachersInCondition)
        {
            $sql = "SELECT * 
            FROM {block_needtodo} 
            WHERE component = ? 
            AND entityid {$teachersInCondition}";

            $params = array(Enums::MESSANGER);

            return $DB->get_records_sql($sql, $params);
        }
        else
        {
            return null;
        }
    }

    /**
     * Returns teachers from global or local settings.
     * 
     * @return array teachers 
     */
    private function get_teachers()
    {
        return tGet::get_teachers_from_cohort($this->params->cohort);
    }

    /**
     * Returns from users unread messages.
     * 
     * @return string unreaded lines
     */
    private function get_from_users_unread_messages() : string 
    {

        $tooMuchClass = $this->get_hidden_elements_class_for_more_button();
        $messages = '';

        $i = 0;
        foreach($this->data as $fromUser)
        {
            if(cLib::is_item_number_too_large($i)) 
            {
                $class = 'ntd-hidden-box '.$tooMuchClass;
                $childClass = $tooMuchClass.Enums::CHILDS;
            }
            else 
            {
                $class = '';
                $childClass = '';
            }

            $messages.= $this->get_teacher_box($fromUser, $class);
            $messages.= $this->get_unreaded_teacher_messages($fromUser, $childClass);

            $i++;
        }

        if(cLib::is_item_number_too_large($i)) 
        {
            $messages.= cLib::get_show_more_button($tooMuchClass);
        }

        return $messages;
    }

    /**
     * Returns class of hidden elements for more button. 
     * 
     * @return string id of more button
     */
    private function get_hidden_elements_class_for_more_button() : string 
    {
        return Enums::MORE.Enums::OTHER.Enums::MESSAGES.$this->params->instance;
    }

    /**
     * Returns box which display teacher name and number of unreaded messages.
     * 
     * @param stdClass from user
     * @param string class name
     * 
     * @return string teacher box
     */
    private function get_teacher_box(\stdClass $fromUser, string $className) : string 
    {
        $attr = array(
            'class' => 'ntd-expandable ntd-chat-teacher ntd-tooltip '.$className,
            'data-teacher' => $fromUser->teacherid,
            'data-block-instance' => $this->params->instance,
            'data-whose-work' => Enums::NOT_MY_WORK,
            'title' => cLib::get_teacher_contacts($fromUser, $fromUser->teachername)
        );
        $text = $fromUser->teachername.$this->get_unread_count($fromUser);
        return \html_writer::tag('div', $text, $attr);
    }

    /**
     * Returns unread teacher messages.
     * 
     * @param stdClass from user
     * @param string class name
     * 
     * @return string unreaded lines
     */
    private function get_unreaded_teacher_messages(\stdClass $fromUser, string $className) : string 
    {
        $messages = '';

        foreach($fromUser->senders as $sender)
        {
            $class = 'ntd-hidden-box ntd-level-2-other-activities ';
            $class.= 'ntd-cursor-default ntd-tooltip '.$className;

            $attr = array(
                'class' => $class,
                'title' => $this->get_student_title($sender),
                'data-teacher' => $fromUser->teacherid,
                'data-block-instance' => $this->params->instance,
                'data-whose-work' => Enums::NOT_MY_WORK,
                'data-user' => $sender->id
            );
            $text = $sender->name.$this->get_unread_count($sender);

            $messages.= \html_writer::tag('div', $text, $attr);
        }

        return $messages;
    }
    
}
