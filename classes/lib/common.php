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

}
