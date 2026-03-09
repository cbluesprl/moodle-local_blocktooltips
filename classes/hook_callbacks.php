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
 * Injects the AMD module that adds tooltips to the "Add a block" modal.
 *
 * @package   local_blocktooltips
 * @copyright CBlue SRL, support@cblue.be
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class hook_callbacks {

    /**
     * Inject the block tooltips AMD module before footer rendering.
     *
     * Only loads if the user is in editing mode and has the capability
     * to manage blocks, since those are the conditions to see the
     * "Add a block" modal.
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

        // Collect all tooltip descriptions from plugin settings.
        $tooltips = self::get_tooltips();
        if (empty($tooltips)) {
            return;
        }

        // Pass tooltip data directly to the AMD module (no extra AJAX needed).
        $PAGE->requires->js_call_amd(
            'local_blocktooltips/block_tooltips',
            'init',
            [$tooltips]
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
}