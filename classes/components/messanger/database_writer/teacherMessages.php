<?php 

namespace NTD\Classes\Components\Messanger\DatabaseWriter;

use \NTD\Classes\Lib\Getters\Common as cGetter;

class TeachersMessanges 
{

    /** Unread teachers messages prepared for writing to the database.  */
    private $unreadMessages;

    /**
     * Prepares data for class.
     * 
     * @param array teachers 
     */
    function __construct($teachers)
    {
        $this->teachers = $teachers;
        
        $this->init_unread_messages();
    }

    /**
     * Returns unread teachers messages.
     * 
     * @return array unread messages
     */
    public function get_unread_teachers_messages()
    {
        return $this->unreadMessages;
    }

    /**
     * Returns unread chat messages.
     */
    private function init_unread_messages() 
    {
        $teachersUnreadMessages = array();

        foreach($this->teachers as $teacher)
        {
            $structure = $this->get_structure_unread_messages_of_teacher($teacher);
            $teacherMessages = $this->get_all_messages_sent_to_teacher($teacher->id);

            foreach($teacherMessages as $message)
            {
                if($this->is_teacher_not_read_message($message))
                {
                    if($this->is_sender_with_unread_message_exists($structure, $message))
                    {
                        $this->add_sender_to_from_users_array($structure, $message);
                    }
                    else 
                    {
                        $this->increase_count_of_unread_sender_messages($structure, $message);
                    }

                    $this->update_sender_last_message_time_if_neccessary($structure, $message);
                    $this->update_count_of_unread_messages_from_all_senders($structure);
                }

                if($this->is_senders_exists($structure))
                {
                    $this->add_senders_names($structure);
                    $this->sort_senders_by_name($structure);
                    $this->convert_lasttime_timestamp_to_string($structure);

                    $teachersUnreadMessages[] = $structure;
                }
            }
        }

        $this->unreadMessages = $teachersUnreadMessages;
    }

    /**
     * Returns structure for teachers unread messages.
     * 
     * @param \stdClass teacher 
     * 
     * @return \stdClass structure 
     */
    private function get_structure_unread_messages_of_teacher(\stdClass $teacher) 
    {
        $structure = new \stdClass;
        $structure->teacher = new \stdClass;
        $structure->teacher->id = $teacher->id;
        $structure->teacher->email = $teacher->email;
        $structure->teacher->name = $teacher->fullname;
        $structure->teacher->phone1 = $teacher->phone1;
        $structure->teacher->phone2 = $teacher->phone2;
        $structure->unreadedMessages = new \stdClass;
        $structure->unreadedMessages->count = 0;
        $structure->unreadedMessages->fromUsers = array();
        return $structure;
    }

    /**
     * Returns all teacher unreaded messages.
     * 
     * @param int teacher id
     * 
     * @return array of all teacher unreaded messages 
     */
    private function get_all_messages_sent_to_teacher(int $teacherId)
    {
        global $DB;

        $sql = "SELECT m.id, m.useridfrom, mcm.userid AS useridto, m.conversationid, m.timecreated  
                FROM {messages} AS m 
                INNER JOIN {message_conversation_members} AS mcm 
                ON m.conversationid = mcm.conversationid 
                WHERE m.useridfrom <> mcm.userid
                AND mcm.userid = ?";

        $params = array($teacherId);

        return $DB->get_records_sql($sql, $params);
    }

    /**
     * Returns true if teacher not read message.
     * 
     * @param stdClass message
     * 
     * @return bool true if message is readed
     */
    private function is_teacher_not_read_message(\stdClass $message) : bool 
    {
        global $DB;

        $where = array(
            'userid' => $message->useridto,
            'messageid' => $message->id,
            'action' => 1
        );

        return !$DB->record_exists('message_user_actions', $where);
    }

    /**
     * Returns true if sender with unread message exists in from user array.
     * 
     * @param stdClass structure
     * @param stdClass message
     * 
     * @return bool 
     */
    private function is_sender_with_unread_message_exists(\stdClass $structure, \stdClass $message) : bool 
    {
        foreach($structure->unreadedMessages->fromUsers as $messageFrom)
        {
            if($messageFrom->id == $message->useridfrom)
            {
                return false;
            }
        }

        return true;
    }

    /**
     * Adds sender to from users array.
     * 
     * @param stdClass structure
     * @param stdClass message
     * 
     * @return void 
     */
    private function add_sender_to_from_users_array(\stdClass $structure, \stdClass $message) : void 
    {
        $user = new \stdClass;
        $user->id = $message->useridfrom;
        $user->count = 1;
        $user->lasttimestamp = $message->timecreated;
        $user->lasttime = $message->timecreated;

        $structure->unreadedMessages->fromUsers[] = $user;
    }

    /**
     * Increases count of unread sender messages.
     * 
     * @param array structure
     * @param stdClass message
     * 
     * @return void 
     */
    private function increase_count_of_unread_sender_messages(\stdClass $structure, \stdClass $message) : void 
    {
        foreach($structure->unreadedMessages->fromUsers as $fromUser)
        {
            if($fromUser->id == $message->useridfrom)
            {
                $fromUser->count++;
            }
        }
    }

    /**
     * Updates sender last message time if neccessary.
     * 
     * @param stdClass structure
     * @param stdClass message
     * 
     * @return void 
     */
    private function update_sender_last_message_time_if_neccessary(\stdClass $structure, \stdClass $message) : void 
    {
        foreach($structure->unreadedMessages->fromUsers as $fromUser)
        {
            if($fromUser->id == $message->useridfrom)
            {
                if($fromUser->lasttimestamp < $message->timecreated)
                {
                    $fromUser->lasttimestamp = $message->timecreated;
                }
            }
        }
    }

    /**
     * Increments total count of teacher's unread messages.
     * 
     * @param stdClass structure
     * 
     * @return void 
     */
    private function update_count_of_unread_messages_from_all_senders(\stdClass $structure) : void 
    {
        $structure->unreadedMessages->count++;
    }  

    /**
     * Returns true if from users array not empty.
     * 
     * @param stdClass structure
     * 
     * @return bool 
     */
    private function is_senders_exists(\stdClass $structure) : bool 
    {
        if(count($structure->unreadedMessages->fromUsers))
        {
            return true;
        }
        else 
        {
            return false;
        }
    }

    /**
     * Adds names to from users array.
     * 
     * @param array structure
     * 
     * @return void 
     */
    private function add_senders_names(\stdClass $structure) : void 
    {
        foreach($structure->unreadedMessages->fromUsers as $fromUser)
        {
            $fromUser->name = cGetter::get_user($fromUser->id)->fullname;
        }
    }

    /**
     * Sorts senders array by names.
     * 
     * @param stdClass structure
     * 
     * @return void 
     */
    private function sort_senders_by_name(\stdClass $structure) : void 
    {
        usort($structure->unreadedMessages->fromUsers, function($a, $b)
        {
            return strcmp($a->name, $b->name);
        });
    }

    /**
     * Converts lasttime timestamp to human-readable string.
     * 
     * @param stdClass structure
     * 
     * @return void 
     */
    private function convert_lasttime_timestamp_to_string(\stdClass $structure) : void 
    {
        foreach($structure->unreadedMessages->fromUsers as $fromUser)
        {
            $fromUser->lasttime = date('Y-m-d H:m', $fromUser->lasttimestamp);
        }
    }

}
