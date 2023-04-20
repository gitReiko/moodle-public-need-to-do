<?php 

namespace NTD\Classes\Components\Messanger\DatabaseWriter;

require_once $CFG->dirroot.'/message/classes/api.php';

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
            $conversations = \core_message\api::get_conversations($teacher->id);

            if($this->is_teacher_has_unread_messages($conversations))
            {
                $structure = $this->get_structure($teacher);

                foreach($conversations as $conversation)
                {
                    if($this->is_conversation_has_unread_messages($conversation))
                    {
                        $structure->unreadedMessages->count += $conversation->unreadcount;

                        $fromUser = new \stdClass;
                        $fromUser->id = reset($conversation->members)->id;
                        $fromUser->name = reset($conversation->members)->fullname;
                        $fromUser->count = $conversation->unreadcount;
                        $fromUser->lasttime = 0;

                        foreach($conversation->messages as $message)
                        {
                            if($fromUser->lasttime < $message->timecreated)
                            {
                                $fromUser->lasttime = $message->timecreated;
                            }
                        }

                        $structure->unreadedMessages->fromUsers[] = $fromUser;
                    }
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
     * Returns true if teacher has unread messages. 
     * 
     * @param \stdClass $teacher
     * 
     * @return bool 
     */
    private function is_teacher_has_unread_messages(array $conversations) : bool 
    {
        foreach($conversations as $conversation)
        {
            if(!empty($conversation->unreadcount))
            {
                return true;
            }
        }

        return false;
    }

    /**
     * Returns true if conversation has unread messages. 
     * 
     * @param \stdClass $conversation
     * 
     * @return bool 
     */
    private function is_conversation_has_unread_messages(\stdClass $conversation) : bool 
    {
        if(empty($conversation->unreadcount))
        {
            return false;
        }
        else 
        {
            return true;
        }
    }

    /**
     * Returns structure for teachers unread messages.
     * 
     * @param \stdClass teacher 
     * 
     * @return \stdClass structure 
     */
    private function get_structure(\stdClass $teacher) 
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
            $fromUser->lasttime = date('Y-m-d H:m', $fromUser->lasttime);
        }
    }

}
