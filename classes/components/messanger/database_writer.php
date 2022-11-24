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

        print_r($this->teachers);
    }

    /**
     * Writes messanger related information to database.
     * 
     * @return void
     */
    public function write() : void
    {
        $this->remove_unnecessary_teachers();
        // write data to database
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

}
