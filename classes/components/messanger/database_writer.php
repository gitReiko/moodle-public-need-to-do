<?php 

namespace NTD\Classes\Components\Messanger;

use \NTD\Classes\Lib\Getters\Common as cGetter;
use NTD\Classes\Lib\Enums as Enums; 

/**
 * Writes messanger related information to database.
 * 
 * @param array all teachers whose work is monitored by the block
 */
class DatabaseWriter 
{

    /**
     * All teachers whose work is monitored by the block
     */
    private $teachers;

    /**
     * Prepares data for the class.
     * 
     * @param array of all teachers whose work is monitored by the block
     */
    function __construct(array $teachers)
    {
        $this->teachers = $teachers;
        $this->add_unreaded_messages_to_teachers();
    }

    /**
     * Writes messanger related information to database.
     * 
     * @return void
     */
    public function write() : void
    {
        $this->remove_unnecessary_teachers();
        
        foreach($this->teachers as $teacher)
        {

            if($this->is_teacher_have_unreaded_messages($teacher))
            {
                $cache = $this->get_database_record($teacher);

                if($this->is_teacher_exists_in_database($teacher->id))
                {
                    $cache->id = $this->get_record_id($cache);
                    $this->update_record_in_database($cache);
                }
                else 
                {
                    $this->add_record_to_database($cache);
                }
            }
        }
    }

    /**
     * Adds all unreaded messages to teachers.
     * 
     * @return array of teachers with unreaded messages
     */
    private function add_unreaded_messages_to_teachers()
    {
        foreach($this->teachers as $teacher)
        {
            $messages = $this->get_all_teacher_messages($teacher->id);

            if(count($messages)) 
            {
                $teacher->messages = new \stdClass;
                $teacher->messages->count = 0;
                $teacher->messages->fromUsers = array();
            }

            foreach($messages as $message)
            {
                if($this->is_message_readed($message))
                {
                    continue;
                }
                else 
                {
                    if($this->is_message_from_user_doesnt_exist($teacher->messages->fromUsers, $message))
                    {
                        $teacher->messages->fromUsers[] = $message->useridfrom;
                    }

                    $teacher->messages->count++;
                }
            }
        }
    }

    /**
     * Returns all messages send to teacher.
     * 
     * @param int of teacher id
     * 
     * @return array of sended messages 
     */
    private function get_all_teacher_messages(int $teacherId)
    {
        global $DB;

        $sql = "SELECT m.id, m.useridfrom, mcm.userid AS useridto, m.conversationid 
                FROM {messages} AS m 
                INNER JOIN {message_conversation_members} AS mcm 
                ON m.conversationid = mcm.conversationid 
                WHERE m.useridfrom <> mcm.userid
                AND mcm.userid = ?";

        $params = array($teacherId);

        return $DB->get_records_sql($sql, $params);
    }

    /**
     * Returns true if message is readed.
     * 
     * @param stdClass message
     * 
     * @return bool read status of the message
     */
    private function is_message_readed(\stdClass $message) : bool 
    {
        global $DB;

        $where = array(
            'userid' => $message->useridto,
            'messageid' => $message->id,
            'action' => 1
        );

        return $DB->record_exists('message_user_actions', $where);
    }

    /**
     * Returns true if message from user doesn't exist.
     * 
     * @param array of messages from users
     * @param stdClass of current message
     * 
     * @return bool of existence of a message
     */
    private function is_message_from_user_doesnt_exist(array $messagesFrom, \stdClass $message) : bool 
    {
        foreach($messagesFrom as $messageFrom)
        {
            if($messageFrom == $message->useridfrom)
            {
                return false;
            }
        }

        return true;
    }

    /**
     * Removes all unnecessary teacher.
     * 
     * Deletes: suspended users, deleted users
     * or users not more enrolled in the cohort.
     * 
     * The cohort specified in the global block settings
     * 
     */
    private function remove_unnecessary_teachers() : void 
    {
        global $DB;

        $teachersInCondition = cGetter::get_teachers_in_database_condition($this->teachers);

        $sql = "DELETE
                FROM {block_needtodo}
                WHERE component = ?
                AND teacherid NOT {$teachersInCondition}";

        $params = array(Enums::MESSANGER);

        $DB->execute($sql, $params);
    }

    /**
     * Returns true if teacher exists in database.
     * 
     * @param int teacher id
     * 
     * @return bool 
     */
    private function is_teacher_exists_in_database(int $teacherId) : bool 
    {
        global $DB;

        $where = array(
            'component' => Enums::MESSANGER,
            'teacherid' => $teacherId
        );

        return $DB->record_exists('block_needtodo', $where);
    }

    /**
     * Returns database entry.
     * 
     * @param stdClass teacher
     * 
     * @return stdClass cache which stores information about teacher's unread messages
     */
    private function get_database_record(\stdClass $teacher) : \stdClass 
    {
        $cache = new \stdClass;
        $cache->component = Enums::MESSANGER;
        $cache->teacherid = $teacher->id;
        $cache->info = json_encode($teacher->messages);
        $cache->updatetime = time();
        return $cache;
    }

    /**
     * Returns true if teacher have unreaded messages.
     * 
     * @param stdClass teacher
     * 
     * @return bool 
     */
    private function is_teacher_have_unreaded_messages(\stdClass $teacher) : bool 
    {
        if(empty($teacher->messages->count))
        {
            return false;
        }
        else 
        {
            return true;
        }
    }

    /**
     * Adds record to database.
     * 
     * @param stdClass $cache which stores information about teacher's unread messages.
     * 
     * @return void 
     */
    private function add_record_to_database(\stdClass $cache) : void 
    {
        global $DB;
        $DB->insert_record('block_needtodo', $cache);
    }

    /**
     * Returns id of database record.
     * 
     * @param stdClass $cache which stores information about teacher's unread messages.
     * 
     * @return id of database record.
     */
    private function get_record_id(\stdClass $cache) : int 
    {
        global $DB;

        $where = array(
            'component' => $cache->component,
            'teacherid' => $cache->teacherid
        );

        return $DB->get_field('block_needtodo', 'id', $where);
    }

    /**
     * Updates record in database.
     * 
     * @param stdClass $cache which stores information about teacher's unread messages. 
     * 
     * @return void 
     */
    private function update_record_in_database($cache) : void 
    {
        global $DB;
        $DB->update_record('block_needtodo', $cache);
    }

}
