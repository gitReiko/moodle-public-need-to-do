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

        print_r($this->data);
        
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

}
