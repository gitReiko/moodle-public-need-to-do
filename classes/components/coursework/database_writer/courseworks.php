<?php 

namespace NTD\Classes\Components\Coursework\DatabaseWriter;

use \NTD\Classes\Lib\Enums as Enums; 

class Coursework 
{
    /** Coursework to be done by teachers. */
    private $courseworks;

    /** Outdated timestamp. Defines by global setting "working_past_days" */
    private $outdatedTimestamp;

    function __construct(int $outdatedTimestamp)
    {
        $this->outdatedTimestamp = $outdatedTimestamp;

        $courseworks = $this->get_sent_for_check_courseworks();
        $courseworks = $this->filter_out_ready_courseworks($courseworks);

        


        print_r($courseworks);

        //echo '<hr>'.$this->outdatedTimestamp;



        // only unchecked

        // get sent for check after date

        // is work not ready

        // add to array

        // then chat messages
        
    }

    /**
     * Returns sent for check courseworks.
     * 
     * @return array sent for check courseworks
     */
    private function get_sent_for_check_courseworks() : ?array 
    {
        global $DB;

        $sql = 'SELECT max(changetime) as senttime, coursework, student
                FROM {coursework_students_statuses} 
                WHERE type = ? 
                AND status = ? 
                AND changetime > ? 
                GROUP BY coursework, student
                ORDER BY coursework, student';

        $params = array(
            'coursework', 
            'sent_for_check',
            $this->outdatedTimestamp
        );

        return $DB->get_records_sql($sql, $params);
    }

    /**
     * Returns only unchecked courseworks.
     * 
     * @param array sent for check courseworks
     * 
     * @return array not checked courseworks
     */
    private function filter_out_ready_courseworks(?array $courseworks) : ?array 
    {
        $unchecked = array();

        foreach($courseworks as $coursework)
        {
            if($this->is_coursework_not_checked($coursework))
            {
                $unchecked[] = $coursework;
            }
        }

        return $unchecked;
    }


    /**
     * Returns true if coursework not checked.
     * 
     * @param stdClass coursework
     * 
     * @return bool 
     */
    private function is_coursework_not_checked(\stdClass $coursework) : bool 
    {
        global $DB;

        $sql = 'SELECT id 
                FROM {coursework_students_statuses}
                WHERE coursework = ?
                AND student = ? 
                AND status = ? 
                AND changetime > ?';

        $params = array(
            $coursework->coursework, 
            $coursework->student, 
            'ready',
            $coursework->senttime
        );

        return !$DB->record_exists_sql($sql, $params);
    }




}
