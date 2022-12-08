<?php 

namespace NTD\Classes\Components\Messanger\Renderer;

require_once __DIR__.'/lib.php';

use NTD\Classes\Lib\Getters\Common as cGetter;
use NTD\Classes\Lib\Common as cLib;
use NTD\Classes\Lib\Enums as Enums; 

/**
 * Returns manager part of block content related to messanger.
 */
class Manager  
{
    /**
     * Block instance config.
     */
    private $config;

    /**
     * Data necessary for rendering
     */
    private $data;

    /**
     * Prepares data for class.
     */
    function __construct($config)
    {
        $this->config = $config;
        $this->prepare_data_for_rendering();
    }

    /**
     * Returns manager part of block content related to messanger.
     * 
     * @return string manager part of block content related to messanger
     */
    public function get_messanger_part() : string 
    {
        $msgr = '';

        if(!empty($this->data))
        {
            $msgr.= Lib::get_messanger_header();
            $msgr.= $this->get_teachers_list();
        }
        
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

        $teachers = $this->get_teachers();
        $teachersInCondition = cGetter::get_teachers_in_database_condition($teachers);

        $sql = "SELECT * 
                FROM {block_needtodo} 
                WHERE component = ? 
                AND teacherid {$teachersInCondition}";

        $params = array(Enums::MESSANGER);

        return $DB->get_records_sql($sql, $params);
    }

    /**
     * Returns teachers from global or local settings.
     * 
     * @return array teachers 
     */
    private function get_teachers()
    {
        if($this->config->use_local_settings)
        {
            return cGetter::get_teachers_for_block_instance($this->config->local_cohort);
        }
        else
        {
            return cGetter::get_teachers_from_global_block_settings();
        }
    }

    /**
     * Returns list of teachers with unreaded messages.
     * 
     * @return string teachers list
     */
    private function get_teachers_list() : string 
    {
        $linkToChat = false;
        $list = '';

        foreach($this->data as $value)
        {
            $list.= Lib::get_teacher_line($value);
            $list.= Lib::get_unreaded_from_lines($value, $linkToChat);
        }

        return $list;
    }

}
