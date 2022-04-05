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
 * Define all the backup steps that will be used by the backup_url_activity_task
 *
 * @package    mod_sertifier
 * @copyright  Sertifier <hr@sertifier.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Define the complete sertifier structure for backup, with file and id annotations
 */
class backup_sertifier_activity_structure_step extends backup_activity_structure_step {

    /**
     * Define the structure for the sertifier activity
     * @return void
     */
    protected function define_structure() {

        // Define each element separated.
        $sertifier = new backup_nested_element('sertifier', array('id'), array(
            'name', 'achievementid', 'description', 'finalquiz',
            'passinggrade', 'completionactivities', 'timecreated', 'certificatename', 'deliveryid'));

        // Define sources.
        $sertifier->set_source_table('sertifier', array('id' => backup::VAR_ACTIVITYID));

        // Define file annotations.
        $sertifier->annotate_files('mod_sertifier', 'name', null); // This file area hasn't itemid.

        // Return the root element (sertifier), wrapped into standard activity structure.
        return $this->prepare_activity_structure($sertifier);

    }
}
