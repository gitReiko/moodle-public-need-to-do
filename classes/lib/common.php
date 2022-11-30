<?php 

namespace NTD\Classes\Lib;

class Common 
{

    /**
     * Returns true if user is site manager.
     * 
     * @return bool
     */
    public static function is_user_site_manager() : bool 
    {
        $systemcontext = \context_system::instance();

        if (has_capability('block/needtodo:monitorteachersonsite', $systemcontext)) 
        {
            return true;
        }
        else 
        {
            return false;
        }
    }

    //public static function is_user_category_manager()

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
