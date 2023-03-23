<?php 

namespace NTD\Classes\Renderer;

require_once 'activities.php';
require_once __DIR__.'/../components/messanger/renderer/manager.php';

use \NTD\Classes\Lib\Getters\Common as cGetter;
use \NTD\Classes\Lib\Enums as Enums; 

/**
 * Forms part of the block for the manager.
 */
class Manager 
{

    /**
     * Block instance params.
     */
    private $params;

    /**
     * Prepares data.
     */
    function __construct(\stdClass $params)
    {
        $this->params = $params;
    }

    /**
     * Returns part of the block Need to do for the manager.
     * 
     * @return string part of the block Need to do for the manager in html format.
     */
    public function get_manager_part() : string 
    {
        $manager = $this->get_manager_header();
        $manager.= $this->get_messanger_part();
        $manager.= $this->get_activities_part();

        return $manager;
    }

    /**
     * Returns header of manager part.
     * 
     * @return string header of manager part
     */
    private function get_manager_header() : string 
    {
        $attr = array('class' => 'ntd-block-header');
        $text = get_string('other_users_work', 'block_needtodo');
        return \html_writer::tag('p', $text, $attr);
    }

    /**
     * Returns manager part of block content related to messanger.
     * 
     * @return string manager part of block content related to messanger
     */
    private function get_messanger_part() : string 
    {
        $renderer = new \NTD\Classes\Components\Messanger\Renderer\Manager($this->params);
        return $renderer->get_messanger_part();
    }

    /**
     * Returns manager part of block content related to activities.
     * 
     * @return string manager part of block content related to activities
     */
    private function get_activities_part() : string 
    {
        $whoseWork = Enums::OTHER;
        $teachers = cGetter::get_teachers_from_cohort($this->params->cohort);
        $renderer = new Activities($this->params, $teachers, $whoseWork);
        return $renderer->get_activities_part();
    }

}
