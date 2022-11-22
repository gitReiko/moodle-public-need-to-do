<?php 

namespace NTD\Classes\DatabaseWriter;

require_once __DIR__.'/../lib/getters/common.php';

/**
 * Writes data to the database.
 * 
 * This data is subsequently used by the renderer to quickly form a block.
 * 
 */
class Main 
{
    /**
     * All teachers whose work is monitored by the block
     */
    private $teachers;

    /**
     * Prepares data for the class.
     */
    function __construct() 
    {
        //
    }

    /**
     * Returns all teachers whose work is monitored by the block.
     * 
     * @return array of all monitored teachers, if they exist
     * @return null if not
     */
    private function get_all_monitored_teachers() 
    {
        //
    }



}
