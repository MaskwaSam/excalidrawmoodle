/**
 * Entry point for the Moodle Excalidraw bundle.
 * This file creates a global object with React, ReactDOM, and Excalidraw.
 */

import React from 'react';
import ReactDOM from 'react-dom';
import { Excalidraw } from '../../packages/excalidraw';

// Expose to global scope for Moodle integration.
window.React = React;
window.ReactDOM = ReactDOM;
window.ExcalidrawLib = {
  Excalidraw: Excalidraw,
};

console.log('Excalidraw library loaded for Moodle');
