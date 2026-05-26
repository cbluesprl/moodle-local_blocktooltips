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
 * Adds tooltip icons next to each block entry in the "Add a block" modal.
 *
 * Observes the DOM for the modal's block list to appear, then injects
 * a small info icon with a Bootstrap tooltip for each block that has
 * a configured description.
 *
 * @module     local_blocktooltips/block_tooltips
 * @copyright  CBlue SRL, support@cblue.be
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import $ from 'jquery';

/** @var {Object} tooltips Associative object of blockname => description */
let tooltips = {};

/**
 * Inject tooltip icons into block list items inside the modal.
 *
 * @param {HTMLElement} container The container element holding block links.
 */
const injectTooltips = (container) => {
    const blockLinks = container.querySelectorAll('[data-blockname]');
    blockLinks.forEach((link) => {
        // Skip if already processed.
        if (link.querySelector('.local-blocktooltips-icon')) {
            return;
        }

        const blockname = link.getAttribute('data-blockname');
        if (!tooltips[blockname]) {
            return;
        }

        const icon = document.createElement('i');
        icon.className = 'local-blocktooltips-icon fa fa-info-circle text-info ml-2';
        icon.setAttribute('data-bs-toggle', 'tooltip');
        icon.setAttribute('data-bs-placement', 'right');
        icon.setAttribute('data-bs-html', 'false');
        icon.setAttribute('title', tooltips[blockname]);
        icon.setAttribute('tabindex', '0');
        icon.setAttribute('role', 'img');
        icon.setAttribute('aria-label', tooltips[blockname]);

        // Prevent the icon click from triggering the block add link.
        icon.addEventListener('click', (e) => {
            e.preventDefault();
            e.stopPropagation();
        });

        link.style.display = 'flex';
        link.style.justifyContent = 'space-between';
        link.style.alignItems = 'center';
        link.appendChild(icon);

        // Initialize Bootstrap tooltip on the icon.
        $(icon).tooltip({container: 'body'});
    });
};

/**
 * Set up a MutationObserver to watch for the "Add a block" modal content.
 *
 * The modal body is rendered asynchronously via AJAX, so we observe DOM
 * mutations to detect when block list items appear inside a modal.
 */
const observeModal = () => {
    const observer = new MutationObserver((mutations) => {
        for (const mutation of mutations) {
            for (const node of mutation.addedNodes) {
                if (node.nodeType !== Node.ELEMENT_NODE) {
                    continue;
                }
                // Check if this node or its children contain block links.
                const container = node.querySelector?.('.list-group') || (node.classList?.contains('list-group') ? node : null);
                if (container && container.querySelector('[data-blockname]')) {
                    injectTooltips(container);
                }
            }
        }
    });

    observer.observe(document.body, {
        childList: true,
        subtree: true,
    });
};

/**
 * Initialize the block tooltips module.
 *
 * @param {Object} tooltipData Associative object of blockname => description.
 */
export const init = (tooltipData) => {
    tooltips = tooltipData || {};
    observeModal();
};