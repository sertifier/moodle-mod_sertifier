<?php
// This file is part of the Sertifier Certificate module for Moodle - https://moodle.org/
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
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * Add event handlers for the quiz
 *
 * @package    mod_sertifier
 * @copyright  Sertifier <hr@sertifier.com>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace mod_sertifier\privacy;
use core_privacy\local\metadata\collection;

/**
 * Privacy class for requesting user data.
 *
 * @package    mod_sertifier
 * @copyright  Sertifier <hr@sertifier.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class provider implements
    \core_privacy\local\metadata\provider {

    /**
     * Provides meta data that is stored about a user with mod_sertifier
     *
     * @param  collection $collection A collection of meta data items to be added to.
     * @return  collection Returns the collection of metadata.
     */
    public static function get_metadata(collection $collection) : collection {

        $collection->add_external_location_link('sertifier', [
                'email' => 'privacy:metadata:sertifier:email',
                'fullname' => 'privacy:metadata:sertifier:fullname',
            ], 'privacy:metadata:sertifier');

        return $collection;
    }
}
