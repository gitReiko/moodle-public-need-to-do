<?php 

namespace NTD\Classes\Lib;

require_once 'enums.php';

class Common 
{

    /**
     * Returns true if user can monitor all courses. 
     * 
     * @return bool 
     */
    public static function is_user_can_monitor_all_courses() : bool 
    {
        return has_capability('block/needtodo:monitorteachersonsite', \context_system::instance());
    }

    /**
     * Returns teacher contacts prepared for render.
     * 
     * @param stdClass teacher 
     * @param stdClass teacher name
     * 
     * @return string contacts prepared for render
     */
    public static function get_teacher_contacts(\stdClass $teacher, string $teachername) : string 
    {
        $newline = '<br>';

        $contacts = $teachername.$newline;

        if($teacher->timelyRead || $teacher->untimelyRead)
        {
            $contacts.= get_string('total_unread_messages', 'block_needtodo');
            $contacts.= $teacher->timelyRead + $teacher->untimelyRead;
            $contacts.= $newline.get_string('untimely_unread_messages', 'block_needtodo');
            $contacts.= $teacher->untimelyRead.$newline;
        }

        if($teacher->timelyCheck || $teacher->untimelyCheck)
        {
            $contacts.= get_string('total_unchecked_works', 'block_needtodo');
            $contacts.= $teacher->timelyCheck + $teacher->untimelyCheck;
            $contacts.= $newline.get_string('untimely_unchecked_works', 'block_needtodo');
            $contacts.= $teacher->untimelyCheck.$newline;     
        }

        if(!empty($teacher->email))
        {
            $contacts.= '✉ '.$teacher->email.$newline;
        }
        if(!empty($teacher->phone1))
        {
            $contacts.= '☎ '.$teacher->phone1.$newline;
        }
        if(!empty($teacher->phone2))
        {
            $contacts.= '☎ '.$teacher->phone2;
        }

        return $contacts;
    }

    /**
     * Returns true if item number is too large.
     * 
     * @param int $number 
     * 
     * @return bool 
     */
    public static function is_item_number_too_large(int $number) : bool 
    {
        if($number > 5) 
        {
            return true;
        }
        else 
        {
            return false;
        }
    }

    /**
     * Returns show / hide more button. 
     * 
     * @param string $class
     * 
     * @return string show / hide more button
     */
    public static function get_show_more_button(string $class) : string 
    {
        $attr = array(
            'class' => 'ntd-more-less-btn',
            'data-show-text' =>  get_string('show_more', 'block_needtodo'),
            'data-hide-text' =>  get_string('hide_more', 'block_needtodo'),
            'onclick' => 'show_hide_more(this,`'.$class.'`,`'.Enums::CHILDS.'`)'
        );
        $text = get_string('show_more', 'block_needtodo');
        return \html_writer::tag('p', $text, $attr);
    }

}
