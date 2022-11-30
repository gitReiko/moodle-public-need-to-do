<?php 

namespace NTD\Classes\Components\Messanger\Renderer;

use NTD\Classes\Lib\Getters\Common as cGetter;
use NTD\Classes\Lib\Common as cLib;
use NTD\Classes\Lib\Enums as Enums; 

/**
 * Returns manager part of block content related to messanger.
 */
class Manager  
{
    /**
     * Data necessary for rendering
     */
    private $data;

    /**
     * Prepares data for class.
     */
    function __construct()
    {
        $this->prepare_data_for_rendering();
    }

    /**
     * Returns manager part of block content related to messanger.
     * 
     * @return string manager part of block content related to messanger
     */
    public function get_messanger_part() : string 
    {
        $msgr = $this->get_messanger_header();
        $msgr.= $this->get_teachers_list();
        
        return $msgr;
    }

    /**
     * Prepares data necessary for rendering.
     */
    private function prepare_data_for_rendering() : void 
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
     * Returns data related to messanger from database.
     * 
     * @return array if data exists
     * @return null if not
     */
    private function get_messanger_needtodo_data() 
    {
        global $DB;

        $teachers = cGetter::get_cohort_teachers_from_global_settings();
        $teachersInCondition = cGetter::get_teachers_in_database_condition($teachers);

        $sql = "SELECT * 
                FROM {block_needtodo} 
                WHERE component = ? 
                AND teacherid {$teachersInCondition}";

        $params = array(Enums::MESSANGER);

        return $DB->get_records_sql($sql, $params);
    }

    /**
     * Returns messanger header. 
     * 
     * @return string messanger header
     */
    private function get_messanger_header() : string 
    {
        $attr = array('style' => 'text-decoration: underline');
        $text = get_string('messages_not_readed_by_users', 'block_needtodo');
        return \html_writer::tag('p', $text, $attr);
    }

    /**
     * Returns list of teachers with unreaded messages.
     * 
     * @return string teachers list
     */
    private function get_teachers_list() : string 
    {
        $list = '';

        foreach($this->data as $value)
        {
            $list.= $this->get_teacher_line($value);
            $list.= $this->get_unreaded_from_lines($value);
        }

        return $list;
    }

    /**
     * Returns line which display teacher name and number of unreaded messages.
     * 
     * @param stdClass all data about one teacher
     * 
     * @return string teacher line
     */
    private function get_teacher_line(\stdClass $value) : string 
    {
        $attr = array('class' => 'ntd-undone-work');
        $text = $value->unreadedMessages->count;
        $unreadedCount = \html_writer::tag('span', $text, $attr);

        $teacherName = $value->teacher->name;

        $attr = array(
            'class' => 'ntd-expandable-box ntd-level-1 ntd-messanger-headline ntd-tooltip',
            'data-teacher' => $value->teacher->id,
            'title' => cLib::get_teacher_contacts($value->teacher)
        );
        $line = $teacherName.' ('.$unreadedCount.')';
        return \html_writer::tag('div', $line, $attr);
    }

    /**
     * Returns lines which display users whose messages are unread.
     * 
     * @param stdClass all data about one teacher
     * 
     * @return string unreaded lines
     */
    private function get_unreaded_from_lines(\stdClass $value) : string 
    {
        $lines = '';

        foreach($value->unreadedMessages->fromUsers as $fromUser)
        {
            $attr = array(
                'class' => 'ntd-hidden-box ntd-level-2',
                'data-teacher' => $value->teacher->id,
                'data-user' => $fromUser->id
            );
            $text = $fromUser->name;
            $lines.= \html_writer::tag('div', $text, $attr);
        }

        return $lines;
    }


}
