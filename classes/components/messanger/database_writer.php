<?php 

namespace NTD\Classes\Components\Messanger;

use NTD\Classes\Lib\Getters\Common as cGetter;
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
        $this->add_teachers_data_to_info_field();
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
                $needtodo = $this->get_needtodo_record($teacher);

                if($this->is_needtodo_record_exists_in_database($teacher->id))
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
    }

    /**
     * Adds teachers data to teachers info field.
     * 
     * @return void 
     */
    private function add_teachers_data_to_info_field() : void  
    {
        foreach($this->teachers as $teacher)
        {
            $teacher->info = new \stdClass;
            $teacher->info->teacher = new \stdClass;
            $teacher->info->teacher->id = $teacher->id;
            $teacher->info->teacher->name = $teacher->fullname;
            $teacher->info->teacher->email = $teacher->email;
            $teacher->info->teacher->phone1 = $teacher->phone1;
            $teacher->info->teacher->phone2 = $teacher->phone2;
        }
    }

    /**
     * Adds all unreaded messages to teachers info field.
     * 
     * @return void 
     */
    private function add_unreaded_messages_to_teachers() : void 
    {
        foreach($this->teachers as $teacher)
        {
            $unreadedMessages = $this->get_all_teacher_unreaded_messages($teacher->id);

            if(count($unreadedMessages)) 
            {
                $teacher->info->unreadedMessages = new \stdClass;
                $teacher->info->unreadedMessages->count = 0;
                $teacher->info->unreadedMessages->fromUsers = array();
            }

            foreach($unreadedMessages as $unreaded)
            {
                if($this->is_message_readed($unreaded))
                {
                    continue;
                }
                else 
                {
                    if($this->is_message_from_user_doesnt_exist($teacher->info->unreadedMessages->fromUsers, $unreaded))
                    {
                        $teacher->info->unreadedMessages->fromUsers[] = $this->get_unread_user($unreaded->useridfrom);
                    }
                    else 
                    {
                        $this->increase_unreaded_count($teacher->info->unreadedMessages->fromUsers, $unreaded);
                    }

                    $teacher->info->unreadedMessages->count++;
                }
            }

            if($this->is_messages_users_exists($teacher))
            {
                $this->add_names_to_unread_users($teacher->info->unreadedMessages->fromUsers);
            }
        }
    }

    /**
     * Returns all teacher unreaded messages.
     * 
     * @param int teacher id
     * 
     * @return array of all teacher unreaded messages 
     */
    private function get_all_teacher_unreaded_messages(int $teacherId)
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
     * Returns true if messages users exists.
     * 
     * @param stdClass teacher
     * 
     * @return bool if messages users exists
     */
    private function is_messages_users_exists(\stdClass $teacher) : bool 
    {
        if(empty($teacher->info->unreadedMessages->fromUsers))
        {
            return false;
        }
        else 
        {
            return true;
        }
    }

    /**
     * Adds names to unread users and sorts users by names
     * 
     * @param array messages from users
     * 
     * @return array messages with names
     */
    private function add_names_to_unread_users(array $fromUsers) 
    {
        foreach($fromUsers as $fromUser)
        {
            $temp = cGetter::get_user($fromUser->id);
            $fromUser->name = $temp->fullname;
        }

        usort($fromUsers, function($a, $b)
        {
            return strcmp($a->name, $b->name);
        });
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
            if($messageFrom->id == $message->useridfrom)
            {
                return false;
            }
        }

        return true;
    }

    /**
     * Returns unread user.
     * 
     * @param int user id
     * 
     * @return stdClass 
     */
    private function get_unread_user(int $userId) : \stdClass 
    {
        $user = new \stdClass;
        $user->id = $userId;
        $user->count = 1;
        return $user;
    }

    /**
     * Increases count of student unreaded messages.
     * 
     * @param array unreaded students
     * @param stdClass unreaded item message
     * 
     * @return void 
     */
    private function increase_unreaded_count(array $fromUsers, \stdClass $unreaded) : void 
    {
        foreach($fromUsers as $fromUser)
        {
            if($fromUser->id == $unreaded->useridfrom)
            {
                $fromUser->count++;
            }
        }
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
            'component' => Enums::MESSANGER,
            'teacherid' => $teacherId
        );

        return $DB->record_exists('block_needtodo', $where);
    }

    /**
     * Returns needtodo record for database.
     * 
     * @param stdClass teacher
     * 
     * @return stdClass needtodo record for database
     */
    private function get_needtodo_record(\stdClass $teacher) : \stdClass 
    {
        $needtodo = new \stdClass;
        $needtodo->component = Enums::MESSANGER;
        $needtodo->teacherid = $teacher->id;
        $needtodo->info = json_encode($teacher->info);
        $needtodo->updatetime = time();
        return $needtodo;
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
        if(empty($teacher->info->unreadedMessages->count))
        {
            return false;
        }
        else 
        {
            return true;
        }
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
            'component' => $needtodo->component,
            'teacherid' => $needtodo->teacherid
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

}
