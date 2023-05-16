<?php 

namespace NTD\Classes\Components\Forum\DatabaseWriter;

/**
 * Teacher messages getter for forum component.
 * 
 * Returns teachers with unread messages.
 */
class UnreadPosts   
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
        $this->unreadMessages = array();

        foreach($this->teachers as $teacher)
        {     
            $post = new \stdClass;
            $post->teacherid = $teacher->id;
            $post->teacheremail = $teacher->email;
            $post->teachername = $teacher->fullname;
            $post->teacherphone1 = $teacher->phone1;
            $post->teacherphone2 = $teacher->phone2;

            foreach($this->forums as $forum)
            {
                $messages = $this->get_teacher_unreaded_forum_messages($teacher, $forum);

                if($messages->timelyRead || $messages->untimelyRead)
                {
                    $post->forumid = $forum->id;
                    $post->forumname = $forum->name;
                    $post->forumcmid = $forum->cmid;
                    $post->courseid = $forum->courseid;
                    $post->coursename = $forum->coursename;
                    $post->untimelyRead = $messages->untimelyRead;
                    $post->timelyRead = $messages->timelyRead;
    
                    $this->unreadMessages[] = $post;
                }
            }
        }
    }

    /**
     * Returns teacher unreaded forum messanges.
     * 
     * @param stdClass teacher 
     * @param stdClass forum
     * 
     * @return stdClass unread messages
     */
    private function get_teacher_unreaded_forum_messages($teacher, $forum) : \stdClass 
    {
        $subscribedToForum = false;

        $msgs = new \stdClass; 
        $msgs->timelyRead = 0;
        $msgs->untimelyRead = 0;
        
        if($this->is_user_has_access_to_forum($forum->cmid, $teacher->id))
        {
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
                    $unread = $this->unread_discussion_posts($teacher->id, $forum->id, $discussion);

                    $msgs->timelyRead = $unread->timelyRead;
                    $msgs->untimelyRead = $unread->untimelyRead;
                }
                else if($this->is_user_subscribed_to_discussion($teacher->id, $forum->id, $discussion->id))
                {
                    $unread = $this->unread_discussion_posts($teacher->id, $forum->id, $discussion);

                    $msgs->timelyRead = $unread->timelyRead;
                    $msgs->untimelyRead = $unread->untimelyRead;
                }
            }
        }
        
        return $msgs;
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
     * Returns unread teacher posts from discussion.
     * 
     * @param int $teacherId
     * @param int $forumId
     * @param stdClass $discussion
     * 
     * @return stdClass unreaded posts 
     */
    private function unread_discussion_posts(int $teacherId, int $forumId, \stdClass $discussion) : \stdClass 
    {
        $unreadPosts = new \stdClass; 
        $unreadPosts->timelyRead = 0;
        $unreadPosts->untimelyRead = 0;

        foreach($discussion->posts as $post)
        {
            if($this->is_user_didnt_read_post($teacherId, $forumId, $discussion->id, $post->id))
            {
                $unreadPosts->timelyRead += $post->timelyRead;
                $unreadPosts->untimelyRead += $post->untimelyRead;
            }
        }

        return $unreadPosts;
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

}
