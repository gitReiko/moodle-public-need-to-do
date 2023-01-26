<?php 

namespace NTD\Classes\Components\Forum\DatabaseWriter;

require_once 'getters/forum.php';
require_once 'getters/teacherMessages.php';

use \NTD\Classes\Components\Forum\DatabaseWriter\Getters\TeacherMessages;
use \NTD\Classes\Components\Forum\DatabaseWriter\Getters\Forum;
use NTD\Classes\Lib\Enums as Enums; 

class Main 
{

    /**
     * All teachers whose work is monitored by the block
     */
    private $teachers;

    /**
     * Level on which data must be updated.
     */
    private $updateLevel;

    /**
     * Forums with all posts.
     */
    private $forums;

    /** Unread teachers messages prepared for writing to the database.  */
    private $unreadMessages;

    /**
     * Prepares data for the class.
     * 
     * @param array of all teachers whose work is monitored by the block
     */
    function __construct(array $teachers, string $updateLevel)
    {
        $this->teachers = $teachers;
        $this->updateLevel = $updateLevel;

        $this->forums = $this->get_forums();
        $this->unreadMessages = $this->get_unread_teachers_messages();
    }

    /**
     * Writes data related to forum into database.
     * 
     * @return void
     */
    public function write() : void 
    {
        if($this->updateLevel === Enums::UPDATE_DATA_ON_SITE_LEVEL)
        {
            //$this->remove_unnecessary_data();
        }

        foreach($this->unreadMessages as $unreadMessage)
        {
            $needtodo = $this->get_needtodo_record($unreadMessage);

            if($this->is_needtodo_record_exists_in_database($unreadMessage->teacher->id))
            {
                $needtodo->id = $this->get_needtodo_record_id($needtodo);
                $this->update_needtodo_record_in_database($needtodo);
            }
            else 
            {
                $this->add_needtodo_record_to_database($needtodo);
            }
        }
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

    /**
     * Returns needtodo record for database.
     * 
     * @param stdClass teacher
     * 
     * @return stdClass needtodo record for database
     */
    private function get_needtodo_record(\stdClass $unreadMessage) : \stdClass 
    {
        $needtodo = new \stdClass;
        $needtodo->component = Enums::FORUM;
        $needtodo->teacherid = $unreadMessage->teacher->id;
        $needtodo->info = json_encode($unreadMessage);
        $needtodo->updatetime = time();
        return $needtodo;
    }

    /**
     * Returns true if needtodo record exists in database.
     * 
     * @param int teacher id
     * 
     * @return bool 
     */
    private function is_needtodo_record_exists_in_database(int $teacherId) : bool 
    {
        global $DB;

        $where = array(
            'component' => Enums::FORUM,
            'teacherid' => $teacherId
        );

        return $DB->record_exists('block_needtodo', $where);
    }

    /**
     * Returns id of database needtodo record.
     * 
     * @param stdClass $needtodo record for database
     * 
     * @return id of database needtodo record.
     */
    private function get_needtodo_record_id(\stdClass $needtodo) : int 
    {
        global $DB;

        $where = array(
            'component' => Enums::FORUM,
            'teacherid' => $needtodo->teacher->id
        );

        return $DB->get_field('block_needtodo', 'id', $where);
    }

    /**
     * Updates needtodo record in database.
     * 
     * @param stdClass $needtodo record for database 
     * 
     * @return void 
     */
    private function update_needtodo_record_in_database($needtodo) : void 
    {
        global $DB;
        $DB->update_record('block_needtodo', $needtodo);
    }

    /**
     * Adds needtodo record to database.
     * 
     * @param stdClass $needtodo record for database
     * 
     * @return void 
     */
    private function add_needtodo_record_to_database(\stdClass $needtodo) : void 
    {
        global $DB;
        $DB->insert_record('block_needtodo', $needtodo);
    }

}
