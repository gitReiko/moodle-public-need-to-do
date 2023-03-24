<?php 

namespace NTD\Classes\Components\Messanger\Renderer;

use NTD\Classes\Lib\Common as cLib;
use NTD\Classes\Lib\Enums as Enums; 

/**
 * Сontains the functions that it uses messanger renderer.
 */
class Lib
{

    /**
     * Returns messanger header. 
     * 
     * @return string messanger header
     */
    public static function get_messanger_header() : string 
    {
        $attr = array('class' => 'ntd-block-subheader');
        $text = get_string('messages_not_read_in_chat', 'block_needtodo');
        return \html_writer::tag('p', $text, $attr);
    }

    /**
     * Returns line which display teacher name and number of unreaded messages.
     * 
     * @param stdClass all data about one teacher
     * @param int block id 
     * @param string $whoseWork 
     * @param string $className
     * 
     * @return string teacher line
     */
    public static function get_teacher_line(\stdClass $value, int $blockInstance, 
        string $whoseWork, string $className) : string 
    {
        $attr = array('class' => 'ntd-undone-work');
        $text = ' <i class="fa fa-comments" aria-hidden="true"></i> ';
        $text.= $value->unreadedMessages->count;
        $unreadedCount = \html_writer::tag('span', $text, $attr);

        $teacherName = $value->teacher->name;

        $attr = array(
            'class' => 'ntd-expandable ntd-chat-teacher ntd-tooltip '.$className,
            'data-teacher' => $value->teacher->id,
            'data-block-instance' => $blockInstance,
            'data-whose-work' => $whoseWork,
            'title' => self::get_teacher_contacts($value->teacher, $value->unreadedMessages->count)
        );
        $line = $teacherName.$unreadedCount;
        return \html_writer::tag('div', $line, $attr);
    }

    /**
     * Returns teacher contacts prepared for render.
     * 
     * @param stdClass teacher 
     * @param int unread count 
     * 
     * @return string contacts prepared for render
     */
    private static function get_teacher_contacts(\stdClass $teacher, int $unreadCount) : string 
    {
        $unreadText = get_string('unread_chat_messages', 'block_needtodo');
        $unreadText.= $unreadCount;

        return cLib::get_teacher_contacts($teacher, $unreadText);
    }

    /**
     * Returns lines which display users whose messages are unread.
     * 
     * @param stdClass all data about one teacher 
     * @param int block id 
     * @param string $whoseWork
     * @param bool $linkToChat
     * 
     * @return string unreaded lines
     */
    public static function get_unreaded_from_lines(\stdClass $value, int $blockInstance,
        string $whoseWork, string $className, bool $linkToChat = false) : string 
    {
        $lines = '';

        foreach($value->unreadedMessages->fromUsers as $fromUser)
        {
            $attr = array('class' => 'ntd-undone-work');
            $text = ' <i class="fa fa-comments" aria-hidden="true"></i> ';
            $text.= $fromUser->count;
            $unreadedCount = \html_writer::tag('span', $text, $attr);

            $attr = array(
                'data-teacher' => $value->teacher->id,
                'data-block-instance' => $blockInstance,
                'data-whose-work' => $whoseWork,
                'data-user' => $fromUser->id
            );

            if($linkToChat)
            {
                $attr2 = array(
                    'class' => 'ntd-hidden-box ntd-level-2-other-activities ntd-cursor-pointer ntd-tooltip '.$className,
                    'onclick' => 'window.location.replace("/message/index.php");', // redirect to link 
                    'title' => self::get_student_title($fromUser)
                );
            }
            else 
            {
                $attr2 = array(
                    'class' => 'ntd-hidden-box ntd-level-2-other-activities ntd-cursor-default ntd-tooltip '.$className, 
                    'title' => self::get_student_title($fromUser)
                );
            }

            $attr = array_merge($attr, $attr2);

            $text = $fromUser->name.$unreadedCount;
            $lines.= \html_writer::tag('div', $text, $attr);
        }

        return $lines;
    }

    /**
     * Returns title for student row.
     * 
     * @param stdClass from the user who sent the message
     * 
     * @return string title
     */
    private static function get_student_title(\stdClass $fromUser) : string 
    {
        $title = get_string('message_sent_by', 'block_needtodo').': ';
        $title.= $fromUser->name.'<br>';
        $title.= get_string('unread_chat_messages', 'block_needtodo');
        $title.= $fromUser->count.'<br>';
        $title.= get_string('last_name_sent', 'block_needtodo').': ';
        $title.= $fromUser->lasttime;

        return $title;
    }

}
