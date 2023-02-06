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
     * Block instance params.
     */
    private $params;

    /**
     * Data necessary for rendering
     */
    private $data;

    /**
     * Class name for more button.
     */
    private $more = Enums::MORE.Enums::OTHER.Enums::MESSAGES;

    /**
     * Prepares data for class.
     */
    function __construct(\stdClass $params)
    {
        $this->params = $params;
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

        // Teachers may not exist
        if($teachersInCondition)
        {
            $sql = "SELECT * 
            FROM {block_needtodo} 
            WHERE component = ? 
            AND teacherid {$teachersInCondition}";

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
        return cGetter::get_teachers_from_cohort($this->params->cohort);
    }

    /**
     * Returns list of teachers with unreaded messages.
     * 
     * @return string teachers list
     */
    private function get_teachers_list() : string 
    {
        $blockClass = $this->more.$this->params->instance;
        $linkToChat = false;
        $list = '';

        $i = 0;
        foreach($this->data as $value)
        {
            if(cLib::is_item_number_too_large($i)) 
            {
                $class = 'ntd-hidden-box '.$blockClass;
                $childClass = $blockClass.Enums::CHILDS;
            }
            else 
            {
                $class = '';
                $childClass = '';
            }

            $list.= Lib::get_teacher_line(
                $value, 
                $this->params->instance, 
                Enums::NOT_MY_WORK,
                $class
            );
            
            $list.= Lib::get_unreaded_from_lines(
                $value, 
                $this->params->instance, 
                Enums::NOT_MY_WORK,
                $childClass,
                $linkToChat
            );

            $i++;
        }

        if(cLib::is_item_number_too_large($i)) 
        {
            $list.= cLib::get_show_more_button($blockClass);
        }

        return $list;
    }

}
