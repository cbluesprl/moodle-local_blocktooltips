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
 * Adds an eye toggle icon on each block to hide/show the block for students.
 *
 * The icon is placed next to the existing block controls (move, 3-dot menu).
 * It toggles the moodle/block:view capability for the student role via AJAX.
 *
 * @module     local_blocktooltips/student_visibility
 * @copyright  CBlue SRL, support@cblue.be
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import $ from 'jquery';
import Ajax from 'core/ajax';
import {get_string as getString} from 'core/str';

/** @var {Set} hiddenBlockIds Set of block instance IDs hidden for students */
let hiddenBlockIds = new Set();

/**
 * Create the eye toggle icon element for a block.
 *
 * @param {number} instanceId The block instance ID.
 * @param {boolean} isHidden Whether the block is currently hidden for students.
 * @returns {HTMLElement} The eye icon button element.
 */
const createEyeIcon = async(instanceId, isHidden) => {
    const btn = document.createElement('button');
    btn.type = 'button';
    btn.className = 'local-blocktooltips-visibility btn btn-link p-0 border-0';
    btn.dataset.instanceId = instanceId;
    btn.dataset.action = 'toggle-student-visibility';

    await updateEyeIcon(btn, isHidden);

    btn.addEventListener('click', handleToggleClick);

    return btn;
};

/**
 * Update the eye icon appearance based on visibility state.
 *
 * @param {HTMLElement} btn The button element.
 * @param {boolean} isHidden Whether the block is hidden for students.
 */
const updateEyeIcon = async(btn, isHidden) => {
    const titleStr = isHidden
        ? await getString('hiddenforstudents', 'local_blocktooltips')
        : await getString('visibleforstudents', 'local_blocktooltips');

    btn.innerHTML = '';

    const icon = document.createElement('i');
    if (isHidden) {
        icon.className = 'fa fa-eye-slash text-danger';
    } else {
        icon.className = 'fa fa-eye text-success';
    }
    icon.setAttribute('aria-hidden', 'true');

    btn.appendChild(icon);
    btn.setAttribute('title', titleStr);
    btn.setAttribute('aria-label', titleStr);

    // Refresh Bootstrap tooltip.
    $(btn).tooltip('dispose');
    $(btn).tooltip({container: 'body'});
};

/**
 * Handle click on the eye toggle icon.
 *
 * @param {Event} e The click event.
 */
const handleToggleClick = async(e) => {
    e.preventDefault();
    e.stopPropagation();

    const btn = e.currentTarget;
    const instanceId = parseInt(btn.dataset.instanceId, 10);

    // Disable button during request.
    btn.disabled = true;
    btn.style.opacity = '0.5';

    try {
        const result = await Ajax.call([{
            methodname: 'local_blocktooltips_toggle_student_visibility',
            args: {blockinstanceid: instanceId},
        }])[0];

        if (result.hiddenforstudents) {
            hiddenBlockIds.add(instanceId);
        } else {
            hiddenBlockIds.delete(instanceId);
        }

        await updateEyeIcon(btn, result.hiddenforstudents);
    } catch (err) {
        window.console.error('Error toggling student visibility:', err);
    } finally {
        btn.disabled = false;
        btn.style.opacity = '';
    }
};

/**
 * Inject eye icons into all block control areas on the page.
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
        if (controlsDiv.querySelector('[data-action="toggle-student-visibility"]')) {
            continue;
        }

        const isHidden = hiddenBlockIds.has(instanceId);
        const eyeBtn = await createEyeIcon(instanceId, isHidden);

        // Insert the eye icon as the first child of block-controls (before move/3-dots).
        const actionMenu = controlsDiv.querySelector('.action-menu');
        if (actionMenu) {
            actionMenu.parentNode.insertBefore(eyeBtn, actionMenu);
        } else {
            controlsDiv.prepend(eyeBtn);
        }
    }
};

/**
 * Initialize the student visibility module.
 *
 * @param {Array} hiddenIds Array of block instance IDs currently hidden for students.
 */
export const init = (hiddenIds) => {
    hiddenBlockIds = new Set(hiddenIds || []);
    injectEyeIcons();
};