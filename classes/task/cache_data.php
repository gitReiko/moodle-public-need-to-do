<?php 

namespace block_needtodo\task;

require_once $CFG->dirroot.'/blocks/needtodo/classes/database_writer/main.php';

use NTD\Classes\DatabaseWriter\Main as DatabaseWriter;

class cache_data extends \core\task\scheduled_task
{

    /**
     * Return the task's name as shown in admin screens.
     *
     * @return string
     */
    public function get_name() 
    {
        return get_string('cache_data', 'block_needtodo');
    }

    /**
     * Execute the task.
     */
    public function execute() 
    {
        $writer = new DatabaseWriter;
        $writer->write_to_database();
    }

}
