<?php 

namespace NTD\Classes\Lib\Components\Cleaner;

use \NTD\Classes\Lib\Enums as Enums;

class Messanger 
{
    /** All teachers whose work is monitored by the block */
    private $teachers;

    /** Data to be written to the database.  */
    private $data;

    function __construct(?array $teachers, ?array $data)
    {
        $this->teachers = $teachers;
        $this->data = $data;
    }

    /**
     * Cleans outdated data related to messanger component.
     */
    public function clear_outdated_data() : void 
    {
        foreach($this->teachers as $teacher)
        {
            if($this->is_teacher_data_exists_in_database($teacher->id))
            {
                if($this->is_teacher_not_need_to_do_component($teacher->id))
                {
                    $this->delete_outdated_teacher_data($teacher->id);
                }
            }
        }
    }

    /**
     * Returns true if teacher data exists in database. 
     * 
     * @param int teacher id
     * 
     * @return bool  
     */
    private function is_teacher_data_exists_in_database(int $teacherId) : bool 
    {
        global $DB;
        
        $where = array(
            'component' => Enums::MESSANGER, 
            'entityid' => $teacherId
        );

        return $DB->record_exists('block_needtodo', $where);
    }

    /**
     * Returns true if teacher need to do component work.
     * 
     * @param int teacher id
     * 
     * @return bool 
     */
    private function is_teacher_not_need_to_do_component(int $teacherId) : bool 
    {
        foreach($this->data as $entity) 
        {
            if($entity->teacher->id == $teacherId)
            {
                return false;
            }
        }

        return true;
    }

    /**
     * Returns true if teacher data exists in database. 
     * 
     * @param int teacher id
     */
    private function delete_outdated_teacher_data(int $teacherId) : void 
    {
        global $DB;

        $where = array(
            'component' => Enums::MESSANGER, 
            'entityid' => $teacherId
        );

        $DB->delete_records('block_needtodo', $where);
    }

}
