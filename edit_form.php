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
 * Block edit form class for the block_pluginname plugin.
 *
 * @package   block_needtodo
 * @copyright 2022, Denis Makouski khornau@gmail.com
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


class block_needtodo_edit_form extends block_edit_form
{

    protected function specific_definition($mform)
    {
        global $DB;

        // Section header title
        $mform->addElement('header', 'config_header', get_string('block_instance_setup', 'block_needtodo'));

        // Use custom settings for block instance
        $mform->addElement('selectyesno', 'config_use_local_settings', get_string('use_settings_below', 'block_needtodo'));
        $mform->setDefault('config_use_local_settings', 0);

        // Block instance name
        $mform->addElement('text', 'config_block_name', get_string('block_instance_name', 'block_needtodo'), array('maxlength' => 255, 'size' => 50));
        $mform->setType('config_block_name', PARAM_RAW);

        // Cohorts selector
        $cohorts = $DB->get_records('cohort', array(), 'name', 'id,name');

        $cohortsoptions = array();
        
        if($cohorts === null)
        {
            $cohortsoptions += array(0 => get_string('cohort_not_exist', 'block_needtodo'));
        }
        else 
        {
            foreach($cohorts as $cohort)
            {
                $cohortsoptions += array($cohort->id => $cohort->name);
            }
        }

        $orderbylabel = get_string('config_local_cohort', 'block_needtodo');
        $mform->addElement('select', 'config_local_cohort', $orderbylabel, $cohortsoptions);
        $mform->setDefault('config_local_cohort', reset($cohortsoptions));
        $mform->addHelpButton('config_local_cohort', 'config_local_cohort', 'block_needtodo');


    }

}
