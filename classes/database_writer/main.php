<?php 

namespace NTD\Classes\DatabaseWriter;

require_once __DIR__.'/../lib/getters/common.php';
require_once __DIR__.'/../components/messanger/database_writer.php';

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
        $this->teachers = $this->get_all_monitored_teachers();
    }

    /**
     * Writes all necessary for block data into database.
     * 
     * @return void 
     */
    public function write_to_database() : void
    {
        if(is_array($this->teachers))
        {
            $this->write_messsanger($this->teachers);
            // forum posts
            // quiz works
            // assign works
        }
    }

    /**
     * Returns all teachers whose work is monitored by the block.
     * 
     * @return array of all monitored teachers, if they exist
     * @return null if not
     * 
     * @todo handle teachers from custom block instances
     */
    private function get_all_monitored_teachers() 
    {
        $teachersIds = $this->get_teachers_from_global_settings();
        // add teachers ids from custom block instances
        // unique teachers ids

        // add teachers fullnames
        // sort ascending

        return $teachersIds;
    }

    /**
     * Return teachers id from cohort which is defined in the global settings.
     * 
     * @return array of teachers ids, if they exist
     * @return null if not
     */
    private function get_teachers_from_global_settings() 
    {
        return \NTD\Classes\Lib\Getters\Common::get_cohort_teachers_from_global_settings();
    }

    /**
     * Writes messanger related information to database.
     * 
     * @return void
     */
    private function write_messsanger() : void 
    {
        $messangerWriter = new \NTD\Classes\Components\Messanger\DatabaseWriter(
            $this->teachers
        );
        $messangerWriter->write();
    }



}
