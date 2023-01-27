<?php 

namespace NTD\Classes\Components\Forum\DatabaseWriter;

require_once 'getters/forum.php';
require_once 'getters/teacherMessages.php';
require_once __DIR__.'/../../../lib/components/database_writer.php';

use \NTD\Classes\Components\Forum\DatabaseWriter\Getters\TeacherMessages;
use \NTD\Classes\Components\Forum\DatabaseWriter\Getters\Forum;
use \NTD\Classes\Lib\Components\DatabaseWriter;
use \NTD\Classes\Lib\Enums as Enums; 

class Main extends DatabaseWriter 
{

    /** Sets component name. */
    protected function set_component_name() : void  
    {
        $this->componentName = Enums::FORUM;
    }

    /** Prepares data neccessary for database writer. */
    protected function prepare_neccessary_data() : void 
    {
        $this->forums = $this->get_forums();
        $this->data = $this->get_unread_teachers_messages();
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
        $needtodo->teacherid = $dataEntity->teacher->id;
        $needtodo->info = json_encode($dataEntity);
        $needtodo->updatetime = time();
        return $needtodo;
    }

    /**
     * Returns forums with subscription.
     * 
     * @return array forums if exists
     */
    private function get_forums() 
    {
        $forums = new Forum;
        return $forums->get_forums();
    }

    /**
     * Returns teachers with unread messages.
     * 
     * @return array teachers with unread messages
     */
    private function get_unread_teachers_messages() 
    {
        $teachers = new TeacherMessages(
            $this->teachers, $this->forums
        );
        return $teachers->get_unread_teachers_messages();
    }

}
