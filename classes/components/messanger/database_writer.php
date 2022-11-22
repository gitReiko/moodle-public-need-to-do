<?php 

namespace NTD\Classes\Components\Messanger;

/**
 * Writes messanger related information to database.
 * 
 * @param array all teachers whose work is monitored by the block
 */
class DatabaseWriter 
{

    /**
     * All teachers whose work is monitored by the block
     */
    private $teachers;

    /**
     * Prepares data for the class.
     * 
     * @param array of all teachers whose work is monitored by the block
     */
    function __construct(array $teachers)
    {
        $this->teachers = $teachers;

        print_r($this->teachers);
    }

    /**
     * Writes messanger related information to database.
     * 
     * @return void
     */
    public function write() : void
    {
        // write data to database
    }

}
