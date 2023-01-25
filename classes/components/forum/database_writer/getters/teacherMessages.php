<?php 

namespace NTD\Classes\Components\Forum\DatabaseWriter\Getters;

/**
 * Teacher messages getter for forum component.
 * 
 * Returns teachers with unread messages.
 */
class TeacherMessages   
{
    /**
     * All teachers whose work is monitored by the block
     */
    private $teachers;

    /**
     * Forums with all posts.
     */
    private $forums;

    /** Unread teachers messages prepared for writing to the database.  */
    private $unreadMessages;

    /**
     * Prepares data for class.
     * 
     * @param array teachers 
     * @param array forums
     */
    function __construct($teachers, $forums)
    {
        $this->teachers = $teachers; 
        $this->forums = $forums; 
        
        $this->init_unread_messages();
    }

    /**
     * Returns unread teachers messages.
     * 
     * @return array unread messages
     */
    public function get_unread_teachers_messages()
    {
        return $this->unreadMessages;
    }

    /**
     * Returns unread messages.
     */
    private function init_unread_messages() 
    {
        $unreadMessages = array();

        foreach($this->teachers as $teacher)
        {     
            $message = new \stdClass;
            $message->teacher = new \stdClass;
            $message->teacher->id = $teacher->id;
            $message->teacher->email = $teacher->email;
            $message->teacher->name = $teacher->fullname;
            $message->teacher->phone1 = $teacher->phone1;
            $message->teacher->phone2 = $teacher->phone2;
            
            $forums = array();
            foreach($this->forums as $forum)
            {
                $forumWithMessages = $this->get_teacher_unreaded_forum_messages($teacher, $forum);

                if($forumWithMessages)
                {
                    $forums[] = $forumWithMessages;
                }
            }

            if(count($forums))
            {
                $message->teacher->forums = $forums;

                $message->teacher->coursesIds = $this->get_unique_courses_ids_from_forums($forums);
                $message->teacher->forumsIds = $this->get_forums_ids_array($forums);

                $unreadMessages[] = $message;
            }
        }

        $this->unreadMessages = $unreadMessages;
    }

    /**
     * Returns teacher unreaded forum messanges.
     * 
     * @param stdClass teacher 
     * @param stdClass forum
     * 
     * @return stdClass $forum
     */
    private function get_teacher_unreaded_forum_messages($teacher, $forum)  
    {
        if($this->is_user_has_access_to_forum($forum->cmid, $teacher->id))
        {
            $subscribedToForum = false;
            $unreadedMessages = 0;
    
            if($forum->forcesubscribe) 
            {                
                $subscribedToForum = true;
            }
            else if($this->is_user_subscribed_to_forum($teacher->id, $forum->id))
            {
                $subscribedToForum = true;
            }

            foreach($forum->discussions as $discussion)
            {
                if($subscribedToForum)
                {
                    $unreadedMessages += $this->unread_discussion_posts($teacher->id, $forum->id, $discussion);
                }
                else if($this->is_user_subscribed_to_discussion($teacher->id, $forum->id, $discussion->id))
                {
                    $unreadedMessages += $this->unread_discussion_posts($teacher->id, $forum->id, $discussion);
                }
            }
    
            if($unreadedMessages)
            {
                return $this->get_forum_with_unread_messages($teacher, $forum, $unreadedMessages);
            }
            else 
            {
                return null;
            }
        }
        else 
        {
            return null;
        }
    }

    /**
     * Returns true if user has access to forum. 
     * 
     * @param int forumId 
     * @param int teacherId
     * 
     * @return bool 
     */
    private function is_user_has_access_to_forum(int $forumId, int $teacherId) : bool 
    {
        $contextmodule = \context_module::instance($forumId);

        if(has_capability('mod/forum:viewdiscussion', $contextmodule, $teacherId)) 
        {
            return true;
        }
        else 
        {
            return false;
        }
    }

    /**
     * Returns true if user subscribed to forum.
     * 
     * @param int $userId
     * @param int $forumId
     * 
     * @return bool 
     */
    private function is_user_subscribed_to_forum(int $userId, int $forumId) : bool 
    {
        global $DB;
        $where = array('userid' => $userId, 'forum' => $forumId); 
        return $DB->record_exists('forum_subscriptions', $where);
    }

    /**
     * Returns true if user subscribed to discussion.
     * 
     * @param int $userId
     * @param int $forumId
     * @param int $discussionId
     * 
     * @return bool 
     */
    private function is_user_subscribed_to_discussion(int $userId, int $forumId, int $discussionId) : bool 
    {
        global $DB;
        $where = array(
            'userid' => $userId, 
            'forum' => $forumId,
            'discussion' => $discussionId
        ); 
        return $DB->record_exists('forum_discussion_subs', $where);
    }

    /**
     * Returns count of unreaded discussion posts.
     * 
     * @param int $teacherId
     * @param int $forumId
     * @param stdClass $discussion
     * 
     * @return int count of unreaded discussion posts 
     */
    private function unread_discussion_posts(int $teacherId, int $forumId, \stdClass $discussion) : int 
    {
        $unreaded = 0;

        foreach($discussion->posts as $post)
        {
            if($this->is_user_didnt_read_post($teacherId, $forumId, $discussion->id, $post->id))
            {
                $unreaded++;
            }
        }

        return $unreaded;
    }

    /**
     * Return true if post did not read.
     * 
     * @param int $teacherId
     * @param int $forumId
     * @param int $discussionId
     * @param int $postId
     * 
     * @return bool 
     */
    private function is_user_didnt_read_post(int $teacherId, int $forumId, int $discussionId, int $postId) : bool 
    {
        global $DB;

        $where = array(
            'userid' => $teacherId,
            'forumid' => $forumId,
            'discussionid' => $discussionId,
            'postid' => $postId
        );

        return !$DB->record_exists('forum_read', $where);
    }

    /**
     * Returns forum with unreaded messages to teacher. 
     * 
     * @param stdClass $teacher
     * @param stdClass $forum 
     * @param int $unreadedMessages
     * 
     * @return stdClass 
     */
    private function get_forum_with_unread_messages(\stdClass $teacher, \stdClass $forum, int $unreadedMessages)  
    {
        $teacherForum = new \stdClass;
        $teacherForum->id = $forum->id;
        $teacherForum->name = $forum->name;
        $teacherForum->cmid = $forum->cmid;
        $teacherForum->courseId = $forum->courseid;
        $teacherForum->courseName = $forum->coursename;
        $teacherForum->unreadedMessages = $unreadedMessages;

        return $teacherForum;
    }

    /**
     * Returns array of unique courses ids from forums.
     * 
     * @return array courses ids
     */
    private function get_unique_courses_ids_from_forums($forums)
    {
        $courses = array();

        foreach($forums as $forum)
        {
            $courses[] = $forum->courseId;
        }

        $courses = array_unique($courses);

        return $courses;
    }

    /**
     * Returns array of forums ids.
     * 
     * @return array courses ids
     */
    private function get_forums_ids_array($forums)
    {
        $forumsArray = array();

        foreach($forums as $forum)
        {
            $forumsArray[] = $forum->id;
        }

        $forumsArray = array_unique($forumsArray);

        return $forumsArray;
    }

}
