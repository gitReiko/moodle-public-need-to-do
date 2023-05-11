<?php 

namespace NTD\Classes\Components\Forum\DatabaseWriter;

use NTD\Classes\Lib\Getters\Common as cGetter;

/**
 * Forums getter for forum component.
 * 
 * Returns all forums to which educators can subscribe.
 */
class Forum  
{
    const FORCE_SUBSCRIPTION = 1;
    const NO_SUBSCRIPTION = 3;

    /**
     * Forums with subscription that match the settings.
     */
    private $forums;

    /**
     * Timestamp after which a post will be outdated.
     * 
     * Outdated post is considered read.
     */
    private $outdatedPostTime;

    /**
     * Prepares data for class. 
     */
    function __construct() 
    {
        $this->forums = $this->get_all_forums_with_subscription();
        $this->add_course_modules_to_forums();
        $this->simplify_forums_force_subscription();
        $this->add_discussions_to_forums();
        $this->outdatedPostTime = $this->get_outdated_posts_time();
        $this->add_posts_to_forums();
    }

    /**
     * Returns all forums that match the settings.
     * 
     * @return array forums
     */
    public function get_forums()
    {
        return $this->forums;
    }

    /**
     * Returns all forums with subscription from database.
     * 
     * @return array forums
     */
    private function get_all_forums_with_subscription()
    {
        global $DB;

        $sql = 'SELECT f.id, f.name, f.forcesubscribe,  
                f.course as courseid, c.fullname as coursename 
                FROM {forum} as f 
                INNER JOIN {course} as c 
                ON f.course = c.id
                WHERE forcesubscribe <> ?
                ORDER BY c.fullname, f.name';
        $params = array(self::NO_SUBSCRIPTION);

        return $DB->get_records_sql($sql, $params);
    }

    /**
     * Adds course modules id to forums.
     */
    private function add_course_modules_to_forums()
    {
        $moduleId = cGetter::get_module_id('forum');

        foreach($this->forums as $forum)
        {
            $forum->cmid = cGetter::get_course_module_id($forum->courseid, $moduleId, $forum->id);
        }
    }

    /**
     * Simplifies forums force subscriptions.
     * 
     * Sets true if enabled force subscription.
     * False if not.
     * 
     * Forced subscription has many statuses, but we are only interested in one.
     */
    private function simplify_forums_force_subscription() : void 
    {
        foreach($this->forums as $forum)
        {
            if($forum->forcesubscribe == self::FORCE_SUBSCRIPTION)
            {
                $forum->forcesubscribe = true;
            }
            else 
            {
                $forum->forcesubscribe = false;
            }
        }
    }

    /**
     * Adds discussions to forums.
     */
    private function add_discussions_to_forums() : void 
    {
        foreach($this->forums as $forum)
        {
            $forum->discussions = $this->get_forum_discussions($forum->id);
        }
    }

    /**
     * Returns forum discussions.
     * 
     * @param int $forumId
     * 
     * @return array forum discussions if its exists
     */
    private function get_forum_discussions(int $forumId)
    {
        global $DB;
        $where = array(
            'forum' => $forumId
        );
        return $DB->get_records('forum_discussions', $where, '', 'id');
    }

    /**
     * Returns timestamp after which the post is considered read.
     * 
     * @return int timestamp 
     */
    private function get_outdated_posts_time()
    {
        $days = $this->get_old_post_days();
        return time() - ($days * 24 * 3600);
    }

    /**
     * Returns count of days after which a post will be outdated.
     * 
     * Outdated post is considered read.
     * 
     * @return int count of days
     */
    private function get_old_post_days() : int 
    {
        global $DB;
        $where = array('name' => 'forum_oldpostdays');
        return $DB->get_field('config', 'value', $where);
    }

    /**
     * Adds posts to forums.
     */
    private function add_posts_to_forums() 
    {
        foreach($this->forums as $forum)
        {
            foreach($forum->discussions as $discussion)
            {
                $discussion->posts = $this->get_discussion_posts($discussion->id);
            }
        }
    }

    /**
     * Returns discussion posts.
     * 
     * @param int $discussionId
     * 
     * @return array discussion posts if exists
     */
    private function get_discussion_posts(int $discussionId)
    {
        global $DB;
        $sql = 'SELECT id 
                FROM {forum_posts} 
                WHERE discussion = ?
                AND modified > ?
                ';
        $params = array($discussionId, $this->outdatedPostTime);
        return $DB->get_records_sql($sql, $params);
    }

}
