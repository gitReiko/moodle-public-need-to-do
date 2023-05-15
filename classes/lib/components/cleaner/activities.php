<?php 

namespace NTD\Classes\Lib\Components\Cleaner;

use \NTD\Classes\Lib\Enums as Enums;

class Activities 
{
    /** Data to be written to the database.  */
    private $data;

    /** The name of the component that uses the class to write to the database.  */
    private $componentName;

    function __construct(?array $data, string $componentName)
    {
        $this->data = $data;
        $this->componentName = $componentName;
    }

    /**
     * Cleans outdated data related to messanger component.
     */
    public function clear_outdated_data() : void 
    {
        $currents = $this->get_current_component_data();

        foreach($currents as $current)
        {
            if($this->is_current_data_not_up_to_date($current))
            {
                $this->delete_outdated_data($current);
            }
        }
    }

    /**
     * Returns componenet data currently stored in the database. 
     * 
     * @return array componenet data
     */
    private function get_current_component_data() : ?array  
    {
        global $DB;
        $where = array('component' => $this->componentName);
        return $DB->get_records('block_needtodo', $where);
    }

    /**
     * Returns true if current data is not up to date.
     * 
     * @param stdClass $current
     * 
     * @return bool 
     */
    private function is_current_data_not_up_to_date(\stdClass $current) : bool 
    {
        foreach($this->data as $data)
        {
            if($data->courseid == $current->entityid)
            {
                return false;
            }
        }

        return true;
    }

    /**
     * Deletes outdated component data.
     * 
     * @param stdClass $current
     */
    private function delete_outdated_data(\stdClass $current) : void
    {
        global $DB;
        $where = array('id' => $this->current->id);
        $DB->delete_records('block_needtodo', $where);
    }

}
