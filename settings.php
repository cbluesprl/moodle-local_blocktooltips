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
 * Admin settings for local_blocktooltips.
 *
 * Generates one textarea per installed block plugin to configure tooltip descriptions.
 *
 * @package   local_blocktooltips
 * @copyright CBlue SRL, support@cblue.be
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

if ($hassiteconfig) {
    $settings = new admin_settingpage(
        'local_blocktooltips_settings',
        get_string('pluginname', 'local_blocktooltips'),
        'moodle/site:config'
    );

    $settings->add(new admin_setting_heading(
        'local_blocktooltips/heading',
        get_string('settings:heading', 'local_blocktooltips'),
        get_string('settings:heading_desc', 'local_blocktooltips')
    ));

    // Get all installed block plugins sorted alphabetically by their translated title.
    $allblocks = \core_component::get_plugin_list('block');
    $blocklist = [];
    foreach (array_keys($allblocks) as $blockname) {
        $blocklist[$blockname] = get_string('pluginname', 'block_' . $blockname);
    }
    core_collator::asort($blocklist);

    foreach ($blocklist as $blockname => $blocktitle) {
        $settings->add(new admin_setting_configtextarea(
            'local_blocktooltips/tooltip_' . $blockname,
            get_string('settings:tooltip_for', 'local_blocktooltips', $blocktitle),
            get_string('settings:tooltip_for_desc', 'local_blocktooltips'),
            '',
            PARAM_TEXT
        ));
    }

    $ADMIN->add('localplugins', $settings);
}
