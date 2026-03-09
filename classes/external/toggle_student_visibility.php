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

namespace local_blocktooltips\external;

use core_external\external_api;
use core_external\external_function_parameters;
use core_external\external_single_structure;
use core_external\external_value;
use context_block;

/**
 * Toggle student visibility for a block instance.
 *
 * Prevents or allows the student role from viewing a specific block
 * by overriding the moodle/block:view capability at block context level.
 *
 * @package   local_blocktooltips
 * @copyright CBlue SRL, support@cblue.be
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class toggle_student_visibility extends external_api {

    /**
     * Describes the parameters for execute.
     *
     * @return external_function_parameters
     */
    public static function execute_parameters(): external_function_parameters {
        return new external_function_parameters([
            'blockinstanceid' => new external_value(PARAM_INT, 'The block instance ID.'),
        ]);
    }

    /** @var array Archetypes that should lose moodle/block:view (CAP_PREVENT). */
    private const PREVENT_ARCHETYPES = ['student', 'user'];

    /** @var array Archetypes that should keep moodle/block:view (CAP_ALLOW) when hiding. */
    private const ALLOW_ARCHETYPES = ['teacher', 'editingteacher', 'manager', 'coursecreator'];

    /**
     * Toggle moodle/block:view for student visibility on the given block instance.
     *
     * When hiding:
     *  - CAP_PREVENT on student + authenticated user roles.
     *  - CAP_ALLOW on teacher, editing teacher, manager, course creator roles
     *    to compensate the authenticated user prevent.
     *
     * When showing:
     *  - CAP_INHERIT on all roles (removes all overrides).
     *
     * @param int $blockinstanceid The block instance ID.
     * @return array The new visibility state.
     */
    public static function execute(int $blockinstanceid): array {
        global $DB;

        $params = self::validate_parameters(self::execute_parameters(), [
            'blockinstanceid' => $blockinstanceid,
        ]);

        $blockcontext = context_block::instance($params['blockinstanceid']);
        self::validate_context($blockcontext);

        // User must be able to override roles in this context.
        require_capability('moodle/role:override', $blockcontext);

        $preventroles = self::get_roles_by_archetypes(self::PREVENT_ARCHETYPES);
        if (empty($preventroles)) {
            throw new \moodle_exception('nostudentrolefound', 'local_blocktooltips');
        }

        // Determine current state from the first prevented role.
        $firstrole = reset($preventroles);
        $currentpermission = $DB->get_field('role_capabilities', 'permission', [
            'roleid' => $firstrole->id,
            'capability' => 'moodle/block:view',
            'contextid' => $blockcontext->id,
        ]);
        $currentlyhidden = ($currentpermission !== false && (int) $currentpermission === CAP_PREVENT);

        if ($currentlyhidden) {
            // Showing again: remove all overrides (CAP_INHERIT).
            $allroles = $preventroles + self::get_roles_by_archetypes(self::ALLOW_ARCHETYPES);
            foreach ($allroles as $role) {
                role_change_permission($role->id, $blockcontext, 'moodle/block:view', CAP_INHERIT);
            }
        } else {
            // Hiding: prevent student + user, allow staff roles.
            foreach ($preventroles as $role) {
                role_change_permission($role->id, $blockcontext, 'moodle/block:view', CAP_PREVENT);
            }
            $allowroles = self::get_roles_by_archetypes(self::ALLOW_ARCHETYPES);
            foreach ($allowroles as $role) {
                role_change_permission($role->id, $blockcontext, 'moodle/block:view', CAP_ALLOW);
            }
        }

        return [
            'blockinstanceid' => $params['blockinstanceid'],
            'hiddenforstudents' => !$currentlyhidden,
        ];
    }

    /**
     * Get roles matching the given archetypes.
     *
     * @param array $archetypes List of archetype strings.
     * @return array Associative array of roleid => role record.
     */
    public static function get_roles_by_archetypes(array $archetypes): array {
        $roles = [];
        foreach ($archetypes as $archetype) {
            $archetyperoles = get_archetype_roles($archetype);
            foreach ($archetyperoles as $role) {
                $roles[$role->id] = $role;
            }
        }
        return $roles;
    }

    /**
     * Get all roles targeted by the prevent action (student + authenticated user).
     *
     * @return array Array of role records.
     */
    public static function get_target_roles(): array {
        return self::get_roles_by_archetypes(self::PREVENT_ARCHETYPES);
    }

    /**
     * Describes the execute return value.
     *
     * @return external_single_structure
     */
    public static function execute_returns(): external_single_structure {
        return new external_single_structure([
            'blockinstanceid' => new external_value(PARAM_INT, 'The block instance ID.'),
            'hiddenforstudents' => new external_value(PARAM_BOOL, 'Whether the block is now hidden for students.'),
        ]);
    }
}
