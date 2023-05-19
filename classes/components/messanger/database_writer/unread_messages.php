<?php 

namespace NTD\Classes\Components\Messanger\DatabaseWriter;

require_once $CFG->dirroot.'/message/classes/api.php';

use \NTD\Classes\Lib\Getters\Common as cGetter;
use \NTD\Classes\Lib\Enums as Enums; 

class UnreadMessages 
{

    /** Unread teachers messages prepared for writing to the database.  */
    private $unreadMessages;

    /** Untimely timestamp period. */
    private $untimelyTimestampPeriod;

    /** Current timestamp. */
    private $currentTimestamp;

    /** Outdated timestamp. Defines by global setting "working_past_days" */
    private $outdatedTimestamp;

    /**
     * Prepares data for class.
     * 
     * @param array teachers 
     */
    function __construct(?array $teachers)
    {
        $this->teachers = $teachers;

        $this->currentTime = time();

        $this->untimelyTimestampPeriod = get_config('block_needtodo', 'days_to_check') * Enums::SECONDS_IN_DAY;
        
        $this->outdatedTimestamp = $this->currentTime - (get_config('block_needtodo', 'working_past_days') * Enums::SECONDS_IN_DAY);

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
        $unreadMessages = array();

        foreach($this->teachers as $teacher)
        {
            $conversations = \core_message\api::get_conversations($teacher->id);

            if($this->is_teacher_has_unread_messages($conversations))
            {
                $unread = $this->get_unread_structure($teacher);

                foreach($conversations as $conversation)
                {
                    if($this->is_conversation_has_unread_messages($conversation))
                    {
                        $sender = $this->get_conversation_sender($conversation);

                        foreach($conversation->messages as $message)
                        {
                            if($this->is_message_not_outdated($message))
                            {
                                $untimely = $message->timecreated + $this->untimelyTimestampPeriod;
                            
                                if($this->currentTime > $untimely)
                                {
                                    $sender->untimelyRead++;
                                    $unread->untimelyRead++;
                                }
                                else 
                                {
                                    $sender->timelyRead++;
                                    $unread->timelyRead++;
                                }
                            }
                        }

                        $unread->senders[] = $sender;
                    }
                }

                $unreadMessages[] = $unread;
            }
        }

        $this->unreadMessages = $unreadMessages;
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
                foreach($conversation->messages as $message)
                {
                    if($this->is_message_not_outdated($message))
                    {
                        return true;
                    }
                }

                return false;
            }
        }

        return false;
    }

    /**
     * Returns unread structure.
     * 
     * @param \stdClass teacher 
     * 
     * @return \stdClass unread 
     */
    private function get_unread_structure(\stdClass $teacher) 
    {
        $unread = new \stdClass;
        $unread->teacherid = $teacher->id;
        $unread->teachername = $teacher->fullname;
        $unread->email = $teacher->email;
        $unread->phone1 = $teacher->phone1;
        $unread->phone2 = $teacher->phone2;
        $unread->timelyCheck = 0;
        $unread->untimelyCheck = 0;
        $unread->timelyRead = 0;
        $unread->untimelyRead = 0;
        $unread->senders = array();

        return $unread;
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
     * Returns conversation sender.
     * 
     * @param stdClass conversation
     * 
     * @return stdClass conversation
     */
    private function get_conversation_sender(\stdClass $conversation) : \stdClass 
    {
        $sender = new \stdClass;
        $sender->id = reset($conversation->members)->id;
        $sender->name = reset($conversation->members)->fullname;
        $sender->timelyCheck = 0;
        $sender->untimelyCheck = 0;
        $sender->timelyRead = 0;
        $sender->untimelyRead = 0;     

        return $sender;
    }

    /**
     * Returns true if message not outdated. 
     * 
     * Outdated date defines by global setting "working_past_days".
     * 
     * @param stdClass message
     * 
     * @return bool 
     */
    private function is_message_not_outdated(\stdClass $message) : bool 
    {
        if($message->timecreated > $this->outdatedTimestamp)
        {
            return true;
        }
        else 
        {
            return false;
        }
    }
    
}
