<?php 

namespace NTD\Classes\Lib;

require_once 'enums.php';

class Common 
{

    /**
     * Return true if user can monitor other users.
     * 
     * @param int course category id
     * 
     * @return bool 
     */
    public static function is_user_can_monitor_other_users(int $courseCategoryId = null) : bool 
    {
        $systemcontext = \context_system::instance();

        if(has_capability('block/needtodo:monitorteachersonsite', $systemcontext)) 
        {
            return true;
        }

        if($courseCategoryId)
        {
            $categorycontext = context_coursecat::instance($courseCategoryId);

            if(has_capability('block/needtodo:monitorteachersincategory', $categorycontext)) 
            {
                return true;
            }
        }
        
        return false;
    }

    /**
     * Returns teacher contacts prepared for render.
     * 
     * @param stdClass teacher 
     * @param string undone count 
     * 
     * @return string contacts prepared for render
     */
    public static function get_teacher_contacts(\stdClass $teacher, string $undoneCount) : string 
    {
        $newline = '<br>';

        $contacts = $teacher->name.$newline;
        $contacts.= $undoneCount.$newline;

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
            'class' => 'ntd-cursor-pointer',
            'data-show-text' =>  get_string('show_more', 'block_needtodo'),
            'data-hide-text' =>  get_string('hide_more', 'block_needtodo'),
            'onclick' => 'show_hide_more(this,`'.$class.'`,`'.Enums::CHILDS.'`)',
            'style' => 'margin-bottom: 0px'
        );
        $text = get_string('show_more', 'block_needtodo');
        return \html_writer::tag('p', $text, $attr);
    }

}
