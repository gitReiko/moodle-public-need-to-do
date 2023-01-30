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
        $attr = array('class' => 'ntd-messanger-header');
        $text = get_string('messages_not_read_in_chat', 'block_needtodo');
        return \html_writer::tag('p', $text, $attr);
    }

    /**
     * Returns line which display teacher name and number of unreaded messages.
     * 
     * @param stdClass all data about one teacher
     * 
     * @return string teacher line
     */
    public static function get_teacher_line(\stdClass $value, int $blockInstance,
        string $whoseWork) : string 
    {
        $attr = array('class' => 'ntd-undone-work');
        $text = $value->unreadedMessages->count;
        $unreadedCount = \html_writer::tag('span', $text, $attr);

        $teacherName = $value->teacher->name;

        $attr = array(
            'class' => 'ntd-expandable-box ntd-level-1 ntd-messanger-headline ntd-tooltip',
            'data-teacher' => $value->teacher->id,
            'data-block-instance' => $blockInstance,
            'data-whose-work' => $whoseWork,
            'title' => cLib::get_teacher_contacts($value->teacher)
        );
        $line = $teacherName.' ('.$unreadedCount.')';
        return \html_writer::tag('div', $line, $attr);
    }

    /**
     * Returns lines which display users whose messages are unread.
     * 
     * @param stdClass all data about one teacher
     * 
     * @return string unreaded lines
     */
    public static function get_unreaded_from_lines(\stdClass $value, int $blockInstance, 
        string $whoseWork, bool $linkToChat = false) : string 
    {
        $lines = '';

        foreach($value->unreadedMessages->fromUsers as $fromUser)
        {
            $attr = array('class' => 'ntd-undone-work');
            $text = $fromUser->count;
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
                    'class' => 'ntd-hidden-box ntd-level-2 ntd-cursor-pointer ntd-tooltip',
                    'onclick' => 'window.location.replace("/message/index.php");', // redirect to link 
                    'title' => self::get_student_title($fromUser)
                );
            }
            else 
            {
                $attr2 = array(
                    'class' => 'ntd-hidden-box ntd-level-2 ntd-cursor-default ntd-tooltip', 
                    'title' => self::get_student_title($fromUser)
                );
            }

            $attr = array_merge($attr, $attr2);

            $text = $fromUser->name.' ('.$unreadedCount.')';
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
        $title.= get_string('last_name_sent', 'block_needtodo').': ';
        $title.= $fromUser->lasttime;

        return $title;
    }

}
