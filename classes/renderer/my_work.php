<?php 

namespace NTD\Classes\Renderer;

require_once 'activities.php';
require_once __DIR__.'/../components/messanger/renderer/my_work.php';

use \NTD\Classes\Components\Messanger\Renderer\MyWork as MessengerMyWork;
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

        if(empty($messangerPart) && empty($activitiesPart))
        {
            $my = '';
        }
        else 
        {
            $my = $this->get_my_works_header();
            $my.= $messangerPart;
            $my.= $activitiesPart;
        }

        return $my;
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
     * Returns my work part of block related to messanger.
     * 
     * @return string my work part of block related to messanger
     */
    private function get_messanger_part() : string 
    {
        $renderer = new MessengerMyWork($this->params);
        return $renderer->get_messanger_part();
    }

    /**
     * Returns activities part of block.
     * 
     * @return string activities part of block
     */
    private function get_activities_part() : string 
    {
        $whoseWork = Enums::MY;
        $teachers = cGetter::get_teachers_array_with_user_only();
        $renderer = new Activities($this->params, $teachers, $whoseWork);
        return $renderer->get_activities_part();
    }

}
