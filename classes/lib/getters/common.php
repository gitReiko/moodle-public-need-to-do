<?php 

namespace NTD\Classes\Lib\Getters;

/**
 * Contains common block getters.
 */
class Common 
{

    /**
     * Returns user from user table. 
     * 
     * @param int $user id
     * 
     * @return stdClass user from user table 
     */
    public static function get_user(int $userId)
    {
        global $DB;

        $where = array('id' => $userId);

        $user = $DB->get_record('user', $where);

        $user->fullname = fullname($user, true);

        return $user;
    }

    /**
     * Returns id of module. 
     * 
     * @param string $name of module
     * 
     * @return int id of module
     */
    public static function get_module_id(string $name) : int 
    {
        global $DB;
        $where = array('name' => $name);
        return $DB->get_field('modules', 'id', $where);
    }

    /**
     * Returns id of course module. 
     * 
     * @param int $courseId
     * @param int $moduleId
     * @param int $instance
     * 
     * @return int course module id
     */
    public static function get_course_module_id(int $courseId, int $moduleId, int $instance) : int 
    {
        global $DB;
        $where = array(
            'course' => $courseId,
            'module' => $moduleId, 
            'instance' => $instance
        );
        return $DB->get_field('course_modules', 'id', $where);
    }

}
