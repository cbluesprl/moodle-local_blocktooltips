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

        // Student visibility indicator: restricted to course contexts.
        // On /my, user profiles, etc. each user has their own blocks, so the
        // indicator cannot reliably answer "what does a student see".
        if ((int) $context->contextlevel === CONTEXT_COURSE) {
            $hiddenblocks = self::get_hidden_block_instances();
            $PAGE->requires->js_call_amd(
                'local_blocktooltips/student_visibility',
                'init',
                [$hiddenblocks]
            );
        }
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
     * Get block instance IDs that are hidden for students on the current page.
     *
     * Reproduces Moodle's rendering-time visibility logic by simulating a
     * representative student user. For every block instance rendered on the
     * page, the block is considered hidden for students when any of these
     * checks fail:
     *   - moodle/block:view capability in the block context hierarchy,
     *   - applicable_formats() match with the current page type,
     *   - block_positions.visible flag on this page,
     *   - block has non-empty content (get_content / is_empty) when run as
     *     the student; this catches blocks whose content is capability-gated
     *     (e.g. mrtestcoursecreation, configurable_reports) or whose parent
     *     context is a user context the student would not share.
     *
     * @return array List of block instance IDs hidden for students.
     */
    public static function get_hidden_block_instances(): array {
        global $DB, $PAGE, $USER;

        if (!isloggedin() || isguestuser()) {
            return [];
        }

        $studentroles = get_archetype_roles('student');
        if (empty($studentroles)) {
            return [];
        }
        $studentrole = reset($studentroles);

        $studentuser = self::get_representative_student_user($PAGE->context, (int) $studentrole->id);
        if (!$studentuser) {
            return [];
        }

        $instances = self::get_page_block_instances($PAGE);
        if (empty($instances)) {
            return [];
        }

        $pagetype = $PAGE->pagetype;
        $subpage = (string) ($PAGE->subpage ?? '');
        $contextid = (int) $PAGE->context->id;

        [$insql, $inparams] = $DB->get_in_or_equal(array_keys($instances), SQL_PARAMS_NAMED, 'bi');
        $positions = $DB->get_records_sql(
            "SELECT blockinstanceid, visible
               FROM {block_positions}
              WHERE blockinstanceid $insql
                AND contextid = :ctx
                AND pagetype = :pt
                AND subpage = :sp",
            array_merge($inparams, ['ctx' => $contextid, 'pt' => $pagetype, 'sp' => $subpage])
        );

        $hidden = [];
        $saveduser = $USER;

        try {
            \core\session\manager::set_user($studentuser);

            foreach ($instances as $instance) {
                try {
                    $blockcontext = \context_block::instance($instance->id);
                } catch (\Throwable $e) {
                    continue;
                }

                if (!has_capability('moodle/block:view', $blockcontext)) {
                    $hidden[] = (int) $instance->id;
                    continue;
                }

                if (!blocks_name_allowed_in_format($instance->blockname, $pagetype)) {
                    $hidden[] = (int) $instance->id;
                    continue;
                }

                if (isset($positions[$instance->id]) && (int) $positions[$instance->id]->visible === 0) {
                    $hidden[] = (int) $instance->id;
                    continue;
                }

                if (self::block_is_empty_for_student($instance, $PAGE)) {
                    $hidden[] = (int) $instance->id;
                    continue;
                }
            }
        } finally {
            \core\session\manager::set_user($saveduser);
        }

        return array_values(array_unique($hidden));
    }

    /**
     * Collect block instances already loaded on the current page, so the
     * indicator matches exactly the DOM [data-instance-id] elements targeted
     * by the AMD module.
     *
     * @param \moodle_page $page
     * @return array Instance records keyed by id.
     */
    protected static function get_page_block_instances(\moodle_page $page): array {
        $instances = [];

        try {
            $regions = $page->blocks->get_regions();
        } catch (\Throwable $e) {
            return [];
        }

        foreach ($regions as $region) {
            try {
                $blocks = $page->blocks->get_blocks_for_region($region);
            } catch (\Throwable $e) {
                continue;
            }

            foreach ($blocks as $blockobj) {
                if (!($blockobj instanceof \block_base)) {
                    continue;
                }
                if (empty($blockobj->instance) || empty($blockobj->instance->id)) {
                    continue;
                }
                $instances[(int) $blockobj->instance->id] = $blockobj->instance;
            }
        }

        return $instances;
    }

    /**
     * Instantiate a fresh block object and ask it whether it would render as
     * empty — Moodle hides empty blocks from non-editing users, so an empty
     * block effectively means "invisible to students".
     *
     * Uses a fresh block_instance so we do not pollute the admin's already
     * rendered block objects or their cached content.
     *
     * @param \stdClass $instance Row-like object from block_instances.
     * @param \moodle_page $page
     * @return bool
     */
    protected static function block_is_empty_for_student(\stdClass $instance, \moodle_page $page): bool {
        try {
            $freshinstance = clone $instance;
            if (!isset($freshinstance->visible)) {
                $freshinstance->visible = 1;
            }
            if (!isset($freshinstance->blockpositionid)) {
                $freshinstance->blockpositionid = null;
            }

            $blockobj = block_instance($instance->blockname, $freshinstance, $page);
            if (!$blockobj) {
                return false;
            }

            return (bool) $blockobj->is_empty();
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * Pick an active, non-deleted, non-siteadmin user holding the student
     * archetype role in the current context hierarchy.
     *
     * @param \context $context
     * @param int $studentroleid
     * @return \stdClass|null
     */
    protected static function get_representative_student_user(\context $context, int $studentroleid): ?\stdClass {
        $users = get_role_users(
            $studentroleid,
            $context,
            true,
            'u.id',
            'u.id ASC',
            false,
            '',
            0,
            25,
            'u.deleted = 0 AND u.suspended = 0 AND u.confirmed = 1'
        );

        foreach ($users as $user) {
            if (is_siteadmin($user->id)) {
                continue;
            }
            $full = \core_user::get_user($user->id, '*', IGNORE_MISSING);
            if ($full && empty($full->deleted) && empty($full->suspended)) {
                return $full;
            }
        }

        return null;
    }
}