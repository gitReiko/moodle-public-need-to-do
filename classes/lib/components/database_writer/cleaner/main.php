<?php 

namespace NTD\Classes\Lib\Components\DatabaseWriter\Cleaner;

require_once 'activities.php';
require_once 'disabled.php';
require_once 'messanger.php';

use \NTD\Classes\Lib\Enums as Enums;

class Main 
{
    /** All teachers whose work is monitored by the block */
    private $teachers;

    /** Data to be written to the database.  */
    private $data;

    /** The name of the component that uses the class to write to the database.  */
    private $componentName;

    function __construct(?array $teachers, ?array $data, string $componentName)
    {
        $this->teachers = $teachers;
        $this->data = $data;
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
            $this->clean_activities_data();
        }

        $this->clear_disabled_components_data();
    }

    /**
     * Cleans outdated data related to messanger component.
     */
    private function clean_messanger_data() : void 
    {
        $messanger = new Messanger($this->teachers, $this->data);
        $messanger->clear_outdated_data();
    }

    /**
     * Cleans outdated data related to activities components.
     */
    private function clean_activities_data() : void 
    {
        $messanger = new Activities($this->data, $this->componentName);
        $messanger->clear_outdated_data();
    }

    /** 
     * Cleans disabled components data. 
     */
    private function clear_disabled_components_data() : void 
    {
        $cleaner = new DisabledComponentsCleaner();
        $cleaner->clean_disabled_components_data();
    }

}
