<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Block definition class for the block_needtodo plugin.
 *
 * @package   block_needtodo
 * @copyright 2022, Denis Makouski khornau@gmail.com
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once __DIR__.'/classes/renderer/content.php';

class block_needtodo extends block_base {

    /**
     * Blocks instance params.
     */
    private $params;

    /**
     * Initialises the block.
     *
     * @return void
     */
    public function init() {
        $this->title = get_string('pluginname', 'block_needtodo');
    }

    /**
     * Sets block instance params.
     * 
     * Sets block instance name.
     */
    function specialization() 
    {
        $this->params = $this->get_block_instance_params();

        $this->title = $this->params->name;
    }

    /**
     * Gets the block contents.
     *
     * @return string The block HTML.
     */
    public function get_content() {
        global $OUTPUT;

        if ($this->content !== null) {
            return $this->content;
        }

        $this->content = new stdClass();
        $this->content->footer = '';

        $ntdContent = new \NTD\Classes\Renderer\Content($this->params);
        $this->content->text = $ntdContent->get_content();

        $this->page->requires->js('/blocks/needtodo/js/common.js');

        return $this->content;
    }

    /**
     * Defines in which pages this block can be added.
     *
     * @return array of the pages where the block can be added.
     */
    public function applicable_formats() {
        return [
            'admin' => false,
            'site-index' => true,
            'course-view' => true,
            'mod' => false,
            'my' => true,
        ];
    }

    public function has_config() 
    {
        return true;
    }

    public function instance_allow_multiple()
    {
        return true;
    }

    /**
     * Returns block instance params.
     * 
     * From global settings or local block instance.
     * 
     * @return stdClass block instance params
     */
    private function get_block_instance_params()
    {
        $params = new \stdClass;

        if(empty($this->config->use_local_settings))
        {
            $params->instance = $this->instance->id;
            $params->name = get_string('pluginname', 'block_needtodo');
            $params->cohort = get_config('block_needtodo', 'monitored_teachers_cohort');
            $params->use_local_settings = false;
            $params->course_category = false;
        }
        else 
        {
            $params->instance = $this->instance->id;
            $params->name = $this->config->block_name;
            $params->cohort = $this->config->local_cohort;
            $params->use_local_settings = true;
            $params->course_category = $this->config->local_course_category;
        }

        return $params;
    }
    
}
