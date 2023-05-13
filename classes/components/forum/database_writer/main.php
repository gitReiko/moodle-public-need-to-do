<?php 

namespace NTD\Classes\Components\Forum\DatabaseWriter;

require_once __DIR__.'/../../../lib/components/database_writer.php';
require_once 'forum.php';
require_once 'unread_posts.php';
require_once 'course.php';
require_once 'teachers.php';
require_once 'activities.php';

use \NTD\Classes\Lib\Components\DatabaseWriter;
use \NTD\Classes\Lib\Enums as Enums; 

class Main extends DatabaseWriter 
{

    /** An array of courses that have unread forum posts. */
    private $courses = array();

    /** Sets component name. */
    protected function set_component_name() : void
    {
        $this->componentName = Enums::FORUM;
    }

    /** Prepares data neccessary for database writer. */
    protected function prepare_neccessary_data() : void 
    {
        $forums = $this->get_forums();
        $unreadPosts = $this->get_unread_posts($forums);

        foreach($unreadPosts as $post)
        {
            $this->process_course_level($post);
            $this->process_teachers_level($post);
            $this->process_activities_level($post);
        }

        $this->data = $this->courses;
    }

    /**
     * Returns the record to be written to the database.
     * 
     * @param stdClass dataEntity
     * 
     * @return stdClass needtodo record for database
     */
    protected function get_needtodo_record(\stdClass $dataEntity) : \stdClass 
    {
        $needtodo = new \stdClass;
        $needtodo->component = $this->componentName;
        $needtodo->entityid = $dataEntity->courseid;
        $needtodo->info = json_encode($dataEntity);
        $needtodo->updatetime = time();
        return $needtodo;
    }

    /**
     * Returns forums with subscription.
     * 
     * @return array forums if exists
     */
    private function get_forums() 
    {
        $forums = new Forum;
        return $forums->get_forums();
    }

    /**
     * Returns teachers unread posts. 
     * 
     * @param array forums 
     * 
     * @return array unread teachers posts 
     */
    private function get_unread_posts(?array $forums) : ?array 
    {
        $posts = new UnreadPosts(
            $this->teachers, $forums
        );
        return $posts->get_unread_teachers_messages();
    }

    /**
     * Process post on course level.
     * 
     * @param stdClass post 
     */
    private function process_course_level(\stdClass $post) : void 
    {
        $course = new Course($this->courses, $post);
        $this->courses = $course->process_level();
    }

    /**
     * Process post on teachers level.
     * 
     * @param stdClass post 
     */
    private function process_teachers_level(\stdClass $post) : void 
    {
        $teachers = new Teachers($this->courses, $post);
        $this->courses = $teachers->process_level(); 
    }

    /**
     * Process post on activities level.
     * 
     * @param stdClass post
     */
    private function process_activities_level(\stdClass $post) : void 
    {
        $activities = new Activities($this->courses, $post);
        $this->courses = $activities->process_level();
    }

}
