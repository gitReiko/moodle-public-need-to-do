<?php 

namespace NTD\Classes\Lib\Components;

require_once 'cleaner/main.php';

use \NTD\Classes\Lib\Components\Cleaner\Main as Cleaner;
use \NTD\Classes\Lib\Getters\Common as cGetter;
use \NTD\Classes\Lib\Enums as Enums;

/**
 * An abstract class that implements common database writer functions.
 */
abstract class DatabaseWriter 
{

    /** All teachers whose work is monitored by the block */
    protected $teachers;

    /** Level on which data must be updated. */
    protected $updateLevel;

    /** Block instance params. */
    protected $params;
    
    /** The name of the component that uses the class to write to the database.  */
    protected $componentName;

    /** Data to be written to the database.  */
    protected $data;

    /**
     * Prepares data for the class.
     * 
     * @param array of all teachers whose work is monitored by the block
     * @param string level at which data should be updated.
     */
    function __construct(array $teachers, string $updateLevel, \stdClass $params = null)
    {
        $this->teachers = $teachers;
        $this->updateLevel = $updateLevel;
        $this->params = $params;

        $this->set_component_name();
        $this->prepare_neccessary_data();
    }

    /**
     * Writes neccessary data to the database.
     * 
     * @return void
     */
    public function write() : void 
    {
        if($this->is_data_needs_to_be_cleared())
        {
            $this->clear_outdated_data();
        }

        foreach($this->data as $dataEntity)
        {
            $needtodo = $this->get_needtodo_record($dataEntity);

            if($this->is_needtodo_record_exists_in_database($needtodo->entityid))
            {
                $needtodo->id = $this->get_needtodo_record_id($needtodo);
                $this->update_needtodo_record_in_database($needtodo);
            }
            else 
            {
                $this->add_needtodo_record_to_database($needtodo);
            }
        }
    }

    /** Sets component name. */
    abstract protected function set_component_name() : void;

    /** Prepares data neccessary for database writer. */
    abstract protected function prepare_neccessary_data() : void;

    /**
     * Returns the record to be written to the database.
     * 
     * @param stdClass dataEntity
     * 
     * @return stdClass needtodo record for database
     */
    abstract protected function get_needtodo_record(\stdClass $dataEntity) : \stdClass;

    /**
     * Returns true if needtodo record exists in database.
     * 
     * @param int teacher id
     * 
     * @return bool 
     */
    private function is_needtodo_record_exists_in_database(int $teacherId) : bool 
    {
        global $DB;

        $where = array(
            'component' => $this->componentName,
            'entityid' => $teacherId
        );

        return $DB->record_exists('block_needtodo', $where);
    }

    /**
     * Returns id of database needtodo record.
     * 
     * @param stdClass $needtodo record for database
     * 
     * @return id of database needtodo record.
     */
    private function get_needtodo_record_id(\stdClass $needtodo) : int 
    {
        global $DB;
        $where = array(
            'component' => $this->componentName,
            'entityid' => $needtodo->entityid
        );
        return $DB->get_field('block_needtodo', 'id', $where);
    }

    /**
     * Updates needtodo record in database.
     * 
     * @param stdClass $needtodo record for database 
     * 
     * @return void 
     */
    private function update_needtodo_record_in_database($needtodo) : void 
    {
        global $DB;
        $DB->update_record('block_needtodo', $needtodo);
    }

    /**
     * Adds needtodo record to database.
     * 
     * @param stdClass $needtodo record for database
     * 
     * @return void 
     */
    private function add_needtodo_record_to_database(\stdClass $needtodo) : void 
    {
        global $DB;
        $DB->insert_record('block_needtodo', $needtodo);
    }

    /**
     * Returns true if data need to be cleared. 
     * 
     * @return bool 
     */
    private function is_data_needs_to_be_cleared() : bool 
    {
        if($this->updateLevel == Enums::UPDATE_DATA_ON_SITE_LEVEL)
        {
            return true;
        }
        else if(
            isset($this->params->use_local_settings) 
            && empty($this->params->use_local_settings)
        )
        {
            return true;
        }
        else 
        {
            return false;
        }
    }

    /** Clears outdated data. */
    protected function clear_outdated_data() : void 
    {
        $cleaner = new Cleaner(
            $this->teachers, 
            $this->data, 
            $this->componentName
        );
        $cleaner->clear_outdated_data();
    }

}
