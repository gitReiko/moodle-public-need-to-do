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
     * Level on which data must be updated.
     */
    private $updateLevel;

    /**
     * Prepares data for the class.
     * 
     * @param array of all teachers whose work is monitored by the block
     */
    function __construct(array $teachers, string $updateLevel)
    {
        $this->teachers = $teachers;
        $this->updateLevel = $updateLevel;
        $this->prepare_teachers_info_field();
    }

    /**
     * Writes messanger related information to database.
     * 
     * @return void
     */
    public function write() : void
    {
        if($this->updateLevel === Enums::UPDATE_DATA_ON_SITE_LEVEL)
        {
            $this->remove_unnecessary_teachers();
        }
        
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
     * Prepares teachers info field.
     * 
     * Adds all necessary data about unread messages to info field.
     * 
     * Next is used to render messager part of the block.
     * 
     * @return void 
     */
    private function prepare_teachers_info_field() : void 
    {
        foreach($this->teachers as $teacher)
        {
            $this->create_info_field($teacher);
            $this->add_teacher_data_to_info_field($teacher);

            $unreadedMessages = $this->get_all_teacher_unreaded_messages($teacher->id);

            if($this->is_unread_messages_exists($unreadedMessages))
            {
                $this->prepare_unread_messages_structure($teacher);
            }

            foreach($unreadedMessages as $unreaded)
            {
                if($this->is_message_readed_by_teacher($unreaded))
                {
                    continue;
                }
                else 
                {
                    if($this->is_message_from_user_doesnt_exist($teacher, $unreaded))
                    {
                        $this->add_user_to_from_users_array($teacher, $unreaded);
                    }
                    else 
                    {
                        $this->increase_from_user_unread_count($teacher, $unreaded);
                    }

                    $this->update_from_user_last_time_if_necessary($teacher, $unreaded);
                    $this->add_total_count_of_unread_messages($teacher);
                }
            }

            if($this->is_from_users_array_exists($teacher))
            {
                $this->add_names_to_from_users($teacher);
                $this->sort_from_users_by_name($teacher);
                $this->convert_lasttime_to_string($teacher);
            }
        }
    }

    /**
     * Creates info field in teacher item.
     * 
     * @param stdClass teacher
     * 
     * @return void 
     */
    private function create_info_field(\stdClass $teacher) : void 
    {
        $teacher->info = new \stdClass;
    }

    /**
     * Add teacher data to info field.
     * 
     * @param stdClass teacher
     * 
     * @return void 
     */
    private function add_teacher_data_to_info_field(\stdClass $teacher) : void 
    {
        $teacher->info->teacher = new \stdClass;
        $teacher->info->teacher->id = $teacher->id;
        $teacher->info->teacher->name = $teacher->fullname;
        $teacher->info->teacher->email = $teacher->email;
        $teacher->info->teacher->phone1 = $teacher->phone1;
        $teacher->info->teacher->phone2 = $teacher->phone2;
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
     * Returns true if unreaded messages exists.
     * 
     * @param array unreadedMessages if exists
     * @param null unreadedMessages if not 
     * 
     * @return void 
     */
    private function is_unread_messages_exists($unreadedMessages) : bool 
    {
        if(count($unreadedMessages)) 
        {
            return true;
        }
        else 
        {
            return false;
        }
    }

    /**
     * Prepares unread messages structure for further work.
     * 
     * @param stdClass teacher
     * 
     * @return void 
     */
    private function prepare_unread_messages_structure(\stdClass $teacher) : void 
    {
        $teacher->info->unreadedMessages = new \stdClass;
        $teacher->info->unreadedMessages->count = 0;
        $teacher->info->unreadedMessages->fromUsers = array();
    }

    /**
     * Returns true if message is readed by teacher.
     * 
     * @param stdClass message
     * 
     * @return bool true if message is readed
     */
    private function is_message_readed_by_teacher(\stdClass $message) : bool 
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
     * @param stdClass teacher
     * @param stdClass unreaded message
     * 
     * @return bool of existence of a message
     */
    private function is_message_from_user_doesnt_exist(\stdClass $teacher, \stdClass $message) : bool 
    {
        foreach($teacher->info->unreadedMessages->fromUsers as $messageFrom)
        {
            if($messageFrom->id == $message->useridfrom)
            {
                return false;
            }
        }

        return true;
    }

    /**
     * Adds user to from users array.
     * 
     * @param stdClass teacher
     * @param stdClass unreaded message
     * 
     * @return void 
     */
    private function add_user_to_from_users_array(\stdClass $teacher, \stdClass $unreaded) : void 
    {
        $user = new \stdClass;
        $user->id = $unreaded->useridfrom;
        $user->count = 1;
        $user->lasttime = $unreaded->timecreated;

        $teacher->info->unreadedMessages->fromUsers[] = $user;
    }

    /**
     * Increases count of student unreaded messages.
     * 
     * @param array teacher
     * @param stdClass unreaded item message
     * 
     * @return void 
     */
    private function increase_from_user_unread_count(\stdClass $teacher, \stdClass $unreaded) : void 
    {
        foreach($teacher->info->unreadedMessages->fromUsers as $fromUser)
        {
            if($fromUser->id == $unreaded->useridfrom)
            {
                $fromUser->count++;
            }
        }
    }

    /**
     * Updates student last time if time of new message is larger.
     * 
     * @param stdClass teacher
     * @param stdClass unreaded message
     * 
     * @return void 
     */
    private function update_from_user_last_time_if_necessary(\stdClass $teacher, \stdClass $unreaded) : void 
    {
        foreach($teacher->info->unreadedMessages->fromUsers as $fromUser)
        {
            if($fromUser->id == $unreaded->useridfrom)
            {
                if($fromUser->lasttime < $unreaded->timecreated)
                {
                    $fromUser->lasttime = $unreaded->timecreated;
                }
            }
        }
    }

    /**
     * Increments total count of teacher's unread messages.
     * 
     * @param stdClass teacher
     * 
     * @return void 
     */
    private function add_total_count_of_unread_messages(\stdClass $teacher) : void 
    {
        $teacher->info->unreadedMessages->count++;
    }

    /**
     * Returns true if from users array exists.
     * 
     * @param stdClass teacher
     * 
     * @return bool if from users array exists
     */
    private function is_from_users_array_exists(\stdClass $teacher) : bool 
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
     * Adds names to from users array.
     * 
     * @param array teacher
     * 
     * @return void 
     */
    private function add_names_to_from_users(\stdClass $teacher) : void 
    {
        foreach($teacher->info->unreadedMessages->fromUsers as $fromUser)
        {
            $temp = cGetter::get_user($fromUser->id);
            $fromUser->name = $temp->fullname;
        }
    }

    /**
     * Sorts from users array by names.
     * 
     * @param stdClass teacher
     * 
     * @return void 
     */
    private function sort_from_users_by_name(\stdClass $teacher) : void 
    {
        usort($teacher->info->unreadedMessages->fromUsers, function($a, $b)
        {
            return strcmp($a->name, $b->name);
        });
    }

    /**
     * Converts last time the message was sent to human-readable string.
     * 
     * @param stdClass teacher
     * 
     * @return void 
     */
    private function convert_lasttime_to_string(\stdClass $teacher) : void 
    {
        foreach($teacher->info->unreadedMessages->fromUsers as $fromUser)
        {
            $fromUser->lasttime = date('Y-m-d H:m', $fromUser->lasttime);
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
