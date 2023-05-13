<?php 

namespace NTD\Classes\Lib\Components\Cleaner;

require_once 'messanger.php';

use \NTD\Classes\Lib\Enums as Enums;

class Main 
{
    /** All teachers whose work is monitored by the block */
    private $teachers;

    /** The name of the component that uses the class to write to the database.  */
    private $componentName;

    function __construct(?array $teachers, string $componentName)
    {
        $this->teachers = $teachers;
        $this->componentName = $componentName;
    }

    /**
     * Cleans all outdated data from database.
     */
    public function clear_outdated_data() : void 
    {
        if($this->componentName == Enums::MESSANGER)
        {
            $this->clean_messanger_data();
        }
        else 
        {
            // activity
        }
    }

    /**
     * Cleans outdated data related to messanger component.
     */
    private function clean_messanger_data() : void 
    {
        $messanger = new Messanger($this->teachers);
        $messanger->clear_outdated_data();
    }



}
