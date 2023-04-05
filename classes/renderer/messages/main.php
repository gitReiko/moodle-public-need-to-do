<?php 

namespace NTD\Classes\Renderer\Messages;

/**
 * Forms messanger part of block.
 */
abstract class Main  
{

    /**
     * Block instance params.
     */
    protected $params;

    /**
     * Data necessary for rendering
     */
    protected $data;

    /**
     * Prepares data for class.
     */
    function __construct(\stdClass $params)
    {
        $this->params = $params;
        $this->prepare_data_for_rendering();
    }

    /**
     * Returns messanger part of block.
     * 
     * @return string messanger part
     */
    public function get_messanger_part() : string 
    {
        $msg = '';

        if(!empty($this->data))
        {
            $msg = $this->get_messanger_header();
            $msg.= $this->get_my_unread_messages();
        }
        
        return $msg;
    }

    /**
     * Prepares data necessary for rendering.
     */
    abstract protected function prepare_data_for_rendering() : void ;

    /**
     * Returns messages unread by the teacher.
     * 
     * @return string unread messages
     */
    abstract protected function get_my_unread_messages() : string ;

    /**
     * Returns title for student row.
     * 
     * @param stdClass from the user who sent the message
     * 
     * @return string title
     */
    protected function get_student_title(\stdClass $fromUser) : string 
    {
        $title = get_string('message_sent_by', 'block_needtodo').': ';
        $title.= $fromUser->name.'<br>';
        $title.= get_string('unread_chat_messages', 'block_needtodo');
        $title.= $fromUser->count.'<br>';
        $title.= get_string('last_name_sent', 'block_needtodo').': ';
        $title.= $fromUser->lasttime;

        return $title;
    }

    /**
     * Returns unread messages count.
     * 
     * @param int unreadMessagesCount
     * 
     * @return string unread messages count
     */
    protected function get_unread_count(int $unreadMessagesCount) : string 
    {
        $attr = array('class' => 'ntd-undone-work');
        $text = ' <i class="fa fa-comments" aria-hidden="true"></i> ';
        $text.= $unreadMessagesCount;
        return \html_writer::tag('span', $text, $attr);
    }

    /**
     * Returns messanger header. 
     * 
     * @return string messanger header
     */
    private function get_messanger_header() : string 
    {
        $attr = array('class' => 'ntd-block-subheader');
        $text = get_string('messages_not_read_in_chat', 'block_needtodo');
        return \html_writer::tag('p', $text, $attr);
    }
    
}
