/**
 * Insolvenzo Form – Gutenberg Block Entry
 *
 * Registers the Insolvenzo multi-step insolvency form block.
 * This is a dynamic Gutenberg block:
 * - Block metadata and attributes are defined in block.json
 * - Editor UI is implemented in edit.js (React)
 * - Frontend rendering is handled via PHP (render_callback)
 *
 * This file only wires the block together and does not render markup.
 */


import { registerBlockType } from '@wordpress/blocks';
import './style.scss'; // Wichtig für das CSS im Build
import Edit from './edit.js'; // Importiert die edit.js
import metadata from './block.json'; // Holt die Daten aus der src/block.json

registerBlockType( metadata.name, {
    edit: Edit,
    save: () => null, // Da es ein dynamischer React-Block ist
} );