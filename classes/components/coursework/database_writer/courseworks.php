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

        $unchecked = $this->get_unchecked_courseworks_statuses();


        print_r($unchecked);



        // only unchecked

        // get sent for check after date

        // is work not ready

        // add to array

        // then chat messages
        
    }

    /**
     * Returns unchecked courseworks.
     * 
     * @return array unchecked courseworks
     */
    private function get_unchecked_courseworks_statuses() : ?array 
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







}
