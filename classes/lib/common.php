<?php 

namespace NTD\Classes\Lib;

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
     * 
     * @return string contacts prepared for render
     */
    public static function get_teacher_contacts(\stdClass $teacher) : string 
    {
        $newline = '<br>';

        $contacts = $teacher->name.$newline;

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

}
