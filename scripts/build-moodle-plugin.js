/**
 * Build script for Moodle plugin bundle.
 * Creates a standalone bundle with React, ReactDOM, and Excalidraw.
 */

const esbuild = require('esbuild');
const path = require('path');
const fs = require('fs');

const MOODLE_PLUGIN_DIR = path.resolve(__dirname, '../mod/excalidraw');
const MOODLE_AMD_DIR = path.join(MOODLE_PLUGIN_DIR, 'amd/src');
const MOODLE_STYLES_DIR = path.join(MOODLE_PLUGIN_DIR, 'styles');

// Ensure directories exist.
if (!fs.existsSync(MOODLE_AMD_DIR)) {
  fs.mkdirSync(MOODLE_AMD_DIR, { recursive: true });
}
if (!fs.existsSync(MOODLE_STYLES_DIR)) {
  fs.mkdirSync(MOODLE_STYLES_DIR, { recursive: true });
}

console.log('Building Excalidraw bundle for Moodle...');

// Build the main Excalidraw bundle.
esbuild.build({
  entryPoints: [path.resolve(__dirname, '../mod/excalidraw/build-entry.js')],
  bundle: true,
  format: 'iife',
  globalName: 'ExcalidrawBundle',
  outfile: path.join(MOODLE_AMD_DIR, 'excalidraw-bundle.js'),
  minify: false,
  sourcemap: true,
  loader: {
    '.woff2': 'dataurl',
    '.ttf': 'dataurl',
  },
  define: {
    'process.env.NODE_ENV': '"production"',
  },
  external: [],
}).then(() => {
  console.log('✓ JavaScript bundle created');

  // Copy CSS file.
  const cssSource = path.resolve(__dirname, '../packages/excalidraw/dist/prod/index.css');
  const cssTarget = path.join(MOODLE_STYLES_DIR, 'excalidraw.css');

  if (fs.existsSync(cssSource)) {
    fs.copyFileSync(cssSource, cssTarget);
    console.log('✓ CSS file copied');
  } else {
    console.log('⚠ Warning: CSS file not found at', cssSource);
    console.log('  Run "yarn build:packages" first to generate the CSS file');
  }

  console.log('✓ Moodle plugin bundle created successfully!');
  console.log('  Bundle location:', MOODLE_AMD_DIR);
}).catch((error) => {
  console.error('Build failed:', error);
  process.exit(1);
});
