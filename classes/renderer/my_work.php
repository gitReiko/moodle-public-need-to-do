<?php 

namespace NTD\Classes\Renderer;

require_once 'activities/my_work.php';
require_once 'messages/my_work.php';

use \NTD\Classes\Renderer\Activities\MyWork as Activities;
use \NTD\Classes\Renderer\Messages\MyWork as Messanger;
use \NTD\Classes\Lib\Getters\Common as cGetter;
use \NTD\Classes\Lib\Enums as Enums; 

/**
 * Forms my work part of the block.
 */
class MyWork
{

    /**
     * Block instance params.
     */
    private $params;

    /**
     * Prepares data.
     * 
     * @param stdClass params of block instance
     */
    function __construct(\stdClass $params)
    {
        $this->params = $params;
    }

    /**
     * Returns my work part of the block.
     * 
     * @return string my work in html format.
     */
    public function get_my_work() : string 
    {
        $messangerPart = $this->get_messanger_part();
        $activitiesPart = $this->get_activities_part();

        $my = $this->get_my_works_header();

        if(empty($messangerPart) && empty($activitiesPart))
        {
            $my.= $this->get_all_work_done();
        }
        else 
        {
            $my.= $messangerPart;
            $my.= $activitiesPart;
        }

        return $my;
    }

    /**
     * Returns a message that all the work is done.
     * 
     * @return string message 
     */
    private function get_all_work_done() : string 
    {
        $attr = array('class' => 'ntd-grey');
        $text = get_string('all_done', 'block_needtodo');
        return \html_writer::tag('p', $text, $attr);
    }

    /**
     * Returns my work header.
     * 
     * @return string my work header
     */
    private function get_my_works_header() : string 
    {
        $attr = array('class' => 'ntd-block-header');
        $text = get_string('my_work', 'block_needtodo');
        return \html_writer::tag('p', $text, $attr);
    }

    /**
     * Returns messanger part of block.
     * 
     * @return string messanger part of block
     */
    private function get_messanger_part() : string 
    {
        $renderer = new Messanger($this->params);
        return $renderer->get_messanger_part();
    }

    /**
     * Returns activities part of block.
     * 
     * @return string activities part of block
     */
    private function get_activities_part() : string 
    {
        $teachers = cGetter::get_teachers_array_with_user_only();
        $renderer = new Activities($this->params, $teachers);
        return $renderer->get_activities_part();
    }

}
