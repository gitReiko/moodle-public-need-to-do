<?php 

namespace NTD\Classes\Lib\Components\DatabaseWriter\Cleaner;

use \NTD\Classes\Lib\Common as cLib;
use \NTD\Classes\Lib\Enums as Enums;

/**
 * This class is responsible for deleting data of disabled components from the database.
 */
class DisabledComponentsCleaner 
{

    /** 
     * Deletes data of disabled components from the database. 
     */
    public function clean_disabled_components_data() : void 
    {
        if($this->is_messanger_component_disabled())
        {
            $this->clean_all_messanger_data();
        }

        if($this->is_assign_component_disabled())
        {
            $this->clean_all_assign_data();
        }

        if($this->is_forum_component_disabled())
        {
            $this->clean_all_forum_data();
        }

        if($this->is_quiz_component_disabled())
        {
            $this->clean_all_quiz_data();
        }

        if(
            $this->is_coursework_module_not_installed()
            ||
            $this->is_coursework_component_disabled()
        )
        {
            $this->clean_all_coursework_data();
        }
    }

    /**
     * Returns true if messanger component disabled.
     * 
     * @return bool
     */
    private function is_messanger_component_disabled() : bool 
    {
        if(get_config('block_needtodo', 'enable_chat_messages'))
        {
            return false;
        }
        else 
        {
            return true;
        }
    }

    /**
     * Cleans all messanger data from database.
     */
    private function clean_all_messanger_data() : void 
    {
        global $DB;
        $where = array('component' => Enums::MESSANGER);
        $DB->delete_records('block_needtodo', $where);
    }

    /**
     * Returns true if assign component disabled.
     * 
     * @return bool
     */
    private function is_assign_component_disabled() : bool 
    {
        if(get_config('block_needtodo', 'enable_assign'))
        {
            return false;
        }
        else 
        {
            return true;
        }
    }

    /**
     * Cleans all messanger data from database.
     */
    private function clean_all_assign_data() : void 
    {
        global $DB;
        $where = array('component' => Enums::ASSIGN);
        $DB->delete_records('block_needtodo', $where);
    }

    /**
     * Returns true if forum component disabled.
     * 
     * @return bool
     */
    private function is_forum_component_disabled() : bool 
    {
        if(get_config('block_needtodo', 'enable_forum'))
        {
            return false;
        }
        else 
        {
            return true;
        }
    }

    /**
     * Cleans all messanger data from database.
     */
    private function clean_all_forum_data() : void 
    {
        global $DB;
        $where = array('component' => Enums::FORUM);
        $DB->delete_records('block_needtodo', $where);
    }

    /**
     * Returns true if quiz component disabled.
     * 
     * @return bool
     */
    private function is_quiz_component_disabled() : bool 
    {
        if(get_config('block_needtodo', 'enable_quiz'))
        {
            return false;
        }
        else 
        {
            return true;
        }
    }

    /**
     * Cleans all messanger data from database.
     */
    private function clean_all_quiz_data() : void 
    {
        global $DB;
        $where = array('component' => Enums::QUIZ);
        $DB->delete_records('block_needtodo', $where);
    }

    /**
     * Return true if coursework module not installed. 
     * 
     * @return bool 
     */
    private function is_coursework_module_not_installed() : bool 
    {
        return !cLib::is_coursework_module_installed();
    }

    /**
     * Returns true if coursework component disabled.
     * 
     * @return bool
     */
    private function is_coursework_component_disabled() : bool 
    {
        if(get_config('block_needtodo', 'enable_coursework'))
        {
            return false;
        }
        else 
        {
            return true;
        }
    }

    /**
     * Cleans all messanger data from database.
     */
    private function clean_all_coursework_data() : void 
    {
        global $DB;
        $where = array('component' => Enums::COURSEWORK);
        $DB->delete_records('block_needtodo', $where);
    }

}
