# Quick Installation Guide for Excalidraw Moodle Plugin

## Prerequisites

- Moodle 5.0.2 or higher
- PHP 8.2 or higher
- Node.js 18.0 or higher
- Yarn package manager

## Quick Start

### 1. Build the Plugin

From the root of this repository:

```bash
# Install dependencies (if not already done)
yarn install

# Build the Excalidraw library and create Moodle bundle
yarn build:moodle
```

This creates:
- `mod/excalidraw/amd/src/excalidraw-bundle.js` - The bundled Excalidraw library
- `mod/excalidraw/styles/excalidraw.css` - Excalidraw styles

### 2. Deploy to Moodle

Copy the plugin to your Moodle installation:

```bash
# Replace /path/to/moodle with your actual Moodle path
cp -r mod/excalidraw /path/to/moodle/mod/
```

Or, if you're developing locally:

```bash
# Create a symlink instead (useful for development)
ln -s $(pwd)/mod/excalidraw /path/to/moodle/mod/excalidraw
```

### 3. Install in Moodle

1. **Log in** to your Moodle site as an administrator

2. **Navigate** to **Site administration** → **Notifications**

3. Moodle will detect the new plugin and show an upgrade notification

4. **Click** "Upgrade Moodle database now"

5. The installation will:
   - Create `mdl_excalidraw` table
   - Create `mdl_excalidraw_submissions` table
   - Register capabilities
   - Install language strings

6. **Verify** the installation at **Site administration** → **Plugins** → **Activity modules** → **Manage activities**

### 4. Test the Plugin

1. Go to any course as a teacher
2. Turn editing on
3. Click "Add an activity or resource"
4. Select "Excalidraw"
5. Fill in the activity details and save
6. Open the activity and verify the drawing canvas loads

## Updating the Plugin

When you make changes:

```bash
# Rebuild
yarn build:moodle

# Copy files again
cp -r mod/excalidraw /path/to/moodle/mod/

# Purge Moodle caches
# Go to: Site administration → Development → Purge all caches
```

## File Permissions

Ensure the web server can read the plugin files:

```bash
# Set proper ownership (adjust user/group for your server)
chown -R www-data:www-data /path/to/moodle/mod/excalidraw

# Set proper permissions
chmod -R 755 /path/to/moodle/mod/excalidraw
```

## Troubleshooting

### "Plugin not found" error

- Check that the directory is named exactly `excalidraw`
- Verify it's in the `mod/` directory, not elsewhere
- Check file permissions

### JavaScript bundle not found

- Make sure you ran `yarn build:moodle`
- Check that `mod/excalidraw/amd/src/excalidraw-bundle.js` exists
- Purge Moodle caches

### Database errors

- Ensure your database user has CREATE TABLE permissions
- Check Moodle's database configuration
- Review PHP error logs

### Still having issues?

Check the detailed README.md for more troubleshooting steps.
