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

namespace local_blocktooltips;

use core\hook\output\before_footer_html_generation;

/**
 * Hook callbacks for local_blocktooltips.
 *
 * Injects AMD modules for block tooltips and student visibility indicator.
 *
 * @package   local_blocktooltips
 * @copyright CBlue SRL, support@cblue.be
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class hook_callbacks {

    /**
     * Inject AMD modules before footer rendering.
     *
     * Loads the tooltip module and the student visibility indicator module
     * when the user is in editing mode with appropriate capabilities.
     *
     * @param before_footer_html_generation $hook
     */
    public static function before_footer_html_generation(before_footer_html_generation $hook): void {
        global $PAGE;

        // Only inject JS when the user is editing and can manage blocks.
        if (!$PAGE->user_is_editing()) {
            return;
        }

        $context = $PAGE->context;
        if (!has_capability('moodle/block:edit', $context)) {
            return;
        }

        // Tooltip feature: inject tooltip data for the "Add a block" modal.
        $tooltips = self::get_tooltips();
        if (!empty($tooltips)) {
            $PAGE->requires->js_call_amd(
                'local_blocktooltips/block_tooltips',
                'init',
                [$tooltips]
            );
        }

        // Student visibility indicator: display eye icon on each block.
        $hiddenblocks = self::get_hidden_block_instances();
        $PAGE->requires->js_call_amd(
            'local_blocktooltips/student_visibility',
            'init',
            [$hiddenblocks]
        );
    }

    /**
     * Retrieve all configured tooltip descriptions from admin settings.
     *
     * @return array Associative array of blockname => description.
     */
    public static function get_tooltips(): array {
        $tooltips = [];
        $allblocks = \core_component::get_plugin_list('block');

        foreach (array_keys($allblocks) as $blockname) {
            $value = get_config('local_blocktooltips', 'tooltip_' . $blockname);
            if (!empty($value)) {
                $tooltips[$blockname] = $value;
            }
        }

        return $tooltips;
    }

    /**
     * Get block instance IDs that are hidden for students.
     *
     * Checks moodle/block:view for the student role at block context level.
     * A block is considered hidden if the student role has a CAP_PREVENT
     * or CAP_PROHIBIT override on moodle/block:view.
     *
     * @return array List of block instance IDs hidden for students.
     */
    public static function get_hidden_block_instances(): array {
        global $DB;

        $studentroles = get_archetype_roles('student');
        if (empty($studentroles)) {
            return [];
        }

        $studentrole = reset($studentroles);

        $sql = "SELECT DISTINCT ctx.instanceid
                  FROM {role_capabilities} rc
                  JOIN {context} ctx ON ctx.id = rc.contextid AND ctx.contextlevel = :contextlevel
                 WHERE rc.roleid = :roleid
                   AND rc.capability = :capability
                   AND rc.permission IN (:prevent, :prohibit)";

        $params = [
            'roleid' => $studentrole->id,
            'capability' => 'moodle/block:view',
            'contextlevel' => CONTEXT_BLOCK,
            'prevent' => CAP_PREVENT,
            'prohibit' => CAP_PROHIBIT,
        ];

        return array_values(array_map('intval', $DB->get_fieldset_sql($sql, $params)));
    }
}