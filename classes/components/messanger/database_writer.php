<?php 

namespace NTD\Classes\Components\Messanger;

use \NTD\Classes\Lib\Getters\Common as cGetter;

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

        $messages = $this->get_teachers_with_all_unreaded_messages();

        // write to database

        print_r($this->teachers);
    }

    /**
     * Writes messanger related information to database.
     * 
     * @return void
     */
    public function write() : void
    {
        // write data to database
    }

    /**
     * Returns all unreaded messages send to teachers.
     * 
     * @return array of teachers with unreaded messages
     */
    private function get_teachers_with_all_unreaded_messages()
    {
        foreach($this->teachers as $teacher)
        {
            $teacher->messagesCount = 0;
            $teacher->messagesFrom = array();

            $messages = $this->get_all_teacher_messages($teacher->id);

            foreach($messages as $message)
            {
                if($this->is_message_readed($message))
                {
                    continue;
                }
                else 
                {
                    if($this->is_message_from_user_doesnt_exist($teacher->messagesFrom, $message))
                    {
                        $teacher->messagesFrom[] = $message->useridfrom;
                    }

                    $teacher->messagesCount++;
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

}
