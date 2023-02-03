<?php 

namespace NTD\Classes\Lib\Components;

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
    function __construct(array $teachers, string $updateLevel)
    {
        $this->teachers = $teachers;
        $this->updateLevel = $updateLevel;

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
        if($this->updateLevel === Enums::UPDATE_DATA_ON_SITE_LEVEL)
        {
            $this->remove_unnecessary_data();
        }

        foreach($this->data as $dataEntity)
        {
            $needtodo = $this->get_needtodo_record($dataEntity);

            if($this->is_needtodo_record_exists_in_database($needtodo->teacherid))
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
     * Removes all unnecessary component data from database.
     * 
     * Deletes: suspended users, deleted users
     * or users not more enrolled in the cohort.
     * 
     * The cohort specified in the global block settings
     * 
     */
    private function remove_unnecessary_data() : void 
    {
        global $DB;

        $teachersInCondition = cGetter::get_teachers_in_database_condition($this->teachers);

        $sql = "DELETE
                FROM {block_needtodo}
                WHERE component = ?
                AND teacherid NOT {$teachersInCondition}";

        $params = array($this->componentName);

        $DB->execute($sql, $params);
    }

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
            'teacherid' => $teacherId
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
            'teacherid' => $needtodo->teacherid
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

}