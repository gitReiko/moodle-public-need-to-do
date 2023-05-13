<?php 

namespace NTD\Classes\Components\Messanger\DatabaseWriter;

require_once 'teacherMessages.php';
require_once __DIR__.'/../../../lib/components/database_writer.php';

use \NTD\Classes\Lib\Components\DatabaseWriter;
use \NTD\Classes\Lib\Enums as Enums; 

/**
 * Writes messanger related information to database.
 * 
 * @param array all teachers whose work is monitored by the block
 */
class Main extends DatabaseWriter 
{

    /** Sets component name. */
    protected function set_component_name() : void
    {
        $this->componentName = Enums::MESSANGER;
    }

    /** Prepares data neccessary for database writer. */
    protected function prepare_neccessary_data() : void
    {
        $teachers = new TeachersMessanges($this->teachers);
        $this->data = $teachers->get_unread_teachers_messages();
    }

    /**
     * Returns the record to be written to the database.
     * 
     * @param stdClass dataEntity
     * 
     * @return stdClass needtodo record for database
     */
    protected function get_needtodo_record(\stdClass $dataEntity) : \stdClass 
    {
        $needtodo = new \stdClass;
        $needtodo->component = $this->componentName;
        $needtodo->entityid = $dataEntity->teacher->id;
        $needtodo->info = json_encode($dataEntity);
        $needtodo->updatetime = time();
        return $needtodo;
    }

}
