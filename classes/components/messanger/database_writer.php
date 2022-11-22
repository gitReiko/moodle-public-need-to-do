<?php 

namespace NTD\Classes\Components\Messanger;

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

        $messages = $this->get_unreaded_messages();

        // mdl_message_popup
        // mdl_messages
        // mdl_message_conversations
        // get message for
        // filter non user

        print_r($messages);
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
     * Returns all unreaded messages.
     * 
     * @return array if messages exists
     * @return null if not
     */
    private function get_unreaded_messages()
    {
        global $DB;

        $sql = 'SELECT m.id, mcm.userid, mcm.conversationid 
                FROM {message_popup} AS mp 
                INNER JOIN {messages} AS m 
                ON mp.messageid = m.id 
                INNER JOIN {message_conversation_members} AS mcm
                ON m.conversationid = mcm.conversationid
                WHERE mp.isread = 0
                AND m.useridfrom <> mcm.userid';

        $params = array();

        return $DB->get_records_sql($sql, $params);
    }

}
