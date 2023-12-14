<?php 

namespace NTD\Classes\Lib\Components\DatabaseWriter\Cleaner;

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

}
