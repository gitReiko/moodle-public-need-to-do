<?php 

namespace NTD\Classes\Components\Messanger\Renderer;

use NTD\Classes\Lib\Getters\Common as cGetter;
use NTD\Classes\Lib\Enums as Enums; 

/**
 * Returns manager part of block content related to messanger.
 */
class Manager  
{

    /**
     * Prepares data for class.
     */
    function __construct()
    {
        $this->get_messanger_data();
    }

    /**
     * Returns manager part of block content related to messanger.
     * 
     * @return string manager part of block content related to messanger
     */
    public function get_messanger_part() : string 
    {
        $msgr = $this->get_messanger_header();
        
        return $msgr;
    }

    private function get_messanger_data() 
    {
        $teachers = cGetter::get_cohort_teachers_from_global_settings();
        $needtodo = $this->get_messanger_needtodo_data($teachers);

        $data = array();
        foreach($needtodo as $value)
        {
            $teacher = $this->get_teacher_from_teachers_array($teachers, $value->teacherid);
            $messages = json_decode($value->info);

            $row = new \stdClass;
            $row->id = $teacher->id;
            $row->name = $teacher->fullname;
            $row->email = $teacher->email;
            $row->phone1 = $teacher->phone1;
            $row->phone2 = $teacher->phone2;
            $row->messagesCount = $messages->unreadedMessages->count;
            $row->fromUsers = $messages->unreadedMessages->fromUsers;

            $data[] = $row;
        }

        print_r($data);



    }

    /**
     * Returns data related to messanger from database.
     * 
     * @return array if data exists
     * @return null if not
     */
    private function get_messanger_needtodo_data($teachers) 
    {
        global $DB;

        $teachersInCondition = cGetter::get_teachers_in_database_condition($teachers);

        $sql = "SELECT *
                FROM {block_needtodo} 
                WHERE component = ? 
                AND teacherid {$teachersInCondition}";

        $params = array(Enums::MESSANGER);

        return $DB->get_records_sql($sql, $params);
    }

    /**
     * Returns teacher found in teachers array.
     * 
     * Teachers its instance of user table. 
     * 
     * @param array teachers
     * @param int id of finding teacher
     * 
     * @return stdClass teacher (instance of user table)
     */
    private function get_teacher_from_teachers_array(array $teachers, int $teacherid) : \stdClass 
    {
        global $DB;

        foreach($teachers as $teacher)
        {
            if($teacher->id == $teacherid)
            {
                return $teacher;
            }
        }
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

}
