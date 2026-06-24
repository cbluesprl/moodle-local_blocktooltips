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
 * Displays a read-only eye icon on each block indicating whether the block
 * is visible or hidden for students (based on moodle/block:view permissions).
 *
 * @module     local_blocktooltips/student_visibility
 * @copyright  CBlue SRL, support@cblue.be
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import {get_string as getString} from 'core/str';
import Log from 'core/log';

/** @var {Set} hiddenBlockIds Set of block instance IDs hidden for students */
let hiddenBlockIds = new Set();

/**
 * Create the read-only eye indicator icon for a block.
 *
 * @param {boolean} isHidden Whether the block is currently hidden for students.
 * @returns {Promise<HTMLElement>} The eye icon element.
 */
const createEyeIcon = async(isHidden) => {
    const titleStr = isHidden
        ? await getString('hiddenforstudents', 'local_blocktooltips')
        : await getString('visibleforstudents', 'local_blocktooltips');

    const icon = document.createElement('span');
    icon.className = 'local-blocktooltips-visibility d-inline-block';
    // Set both the Bootstrap 4 (data-*) and Bootstrap 5 (data-bs-*) attributes so the
    // theme's delegated tooltip initialisation works on Moodle 4.x and 5.x. We do not
    // init via jQuery because Bootstrap 5 (Moodle 5.0) dropped the jQuery plugin and
    // $(icon).tooltip() would throw; the "title" attribute is a graceful fallback.
    icon.setAttribute('data-toggle', 'tooltip');
    icon.setAttribute('data-bs-toggle', 'tooltip');
    icon.setAttribute('data-placement', 'bottom');
    icon.setAttribute('data-bs-placement', 'bottom');
    icon.setAttribute('title', titleStr);
    icon.setAttribute('role', 'img');
    icon.setAttribute('aria-label', titleStr);

    const i = document.createElement('i');
    i.className = isHidden
        ? 'fa fa-eye-slash text-danger'
        : 'fa fa-eye text-success';
    i.setAttribute('aria-hidden', 'true');

    icon.appendChild(i);

    return icon;
};

/**
 * Inject eye indicator icons into all block control areas on the page.
 */
const injectEyeIcons = async() => {
    const blocks = document.querySelectorAll('[data-instance-id]');

    for (const block of blocks) {
        const instanceId = parseInt(block.dataset.instanceId, 10);
        if (!instanceId) {
            continue;
        }

        const controlsDiv = block.querySelector('.block-controls');
        if (!controlsDiv) {
            continue;
        }

        // Skip if already processed.
        if (controlsDiv.querySelector('.local-blocktooltips-visibility')) {
            continue;
        }

        const isHidden = hiddenBlockIds.has(instanceId);
        const eyeIcon = await createEyeIcon(isHidden);

        // Insert before the action menu.
        const actionMenu = controlsDiv.querySelector('.action-menu');
        if (actionMenu) {
            actionMenu.parentNode.insertBefore(eyeIcon, actionMenu);
        } else {
            controlsDiv.prepend(eyeIcon);
        }
    }
};

/**
 * Initialize the student visibility indicator module.
 *
 * @param {Array} hiddenIds Array of block instance IDs currently hidden for students.
 */
export const init = (hiddenIds) => {
    hiddenBlockIds = new Set(hiddenIds || []);
    injectEyeIcons().catch((e) => {
        Log.error('local_blocktooltips/student_visibility: failed to inject icons');
        Log.error(e);
    });
};