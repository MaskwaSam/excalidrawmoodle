# Excalidraw for Moodle

A Moodle activity module plugin that integrates the Excalidraw drawing tool into Moodle courses.

## Features

- **Interactive Drawing**: Create sketches, diagrams, and illustrations directly in Moodle
- **Auto-Save**: Automatically saves your work every 30 seconds
- **Grading Support**: Teachers can grade student submissions
- **File Storage**: All drawings are saved to Moodle's file system
- **User-Friendly**: Simple, intuitive interface for both students and teachers

## Requirements

- Moodle 5.0 or higher
- PHP 8.2 or higher
- Modern web browser (Chrome, Firefox, Safari, Edge)

## Installation

### Step 1: Build the Excalidraw Bundle

Before installing the plugin, you need to build the Excalidraw bundle:

```bash
# Install dependencies
yarn install

# Build the Moodle plugin bundle
yarn build:moodle
```

This will create the necessary JavaScript and CSS files in the `mod/excalidraw` directory.

### Step 2: Install in Moodle

1. Copy the entire `mod/excalidraw` directory to your Moodle installation's `mod/` directory:
   ```bash
   cp -r mod/excalidraw /path/to/moodle/mod/
   ```

2. Log in to Moodle as an administrator

3. Navigate to **Site administration → Notifications**

4. Moodle will detect the new plugin and prompt you to upgrade

5. Click **Upgrade Moodle database now**

6. The plugin tables will be created and the plugin will be installed

### Step 3: Verify Installation

1. Go to **Site administration → Plugins → Activity modules → Manage activities**
2. You should see "Excalidraw" in the list
3. Ensure it is enabled

## Usage

### For Teachers

#### Creating an Excalidraw Activity

1. Turn editing on in your course
2. Click "Add an activity or resource"
3. Select "Excalidraw" from the activity list
4. Configure the activity:
   - **Name**: Give your activity a descriptive name
   - **Description**: Provide instructions for students
   - **Maximum grade**: Set the maximum grade (default: 100)
5. Save and display

#### Grading Submissions

1. Open the Excalidraw activity
2. Click on "View all submissions" (when implemented)
3. View and grade student drawings
4. Provide feedback

### For Students

#### Creating a Drawing

1. Click on the Excalidraw activity in your course
2. Use the drawing tools to create your illustration:
   - Select shapes, lines, arrows, and text
   - Choose colors and styles
   - Add annotations
3. Click **Save drawing** to manually save
4. The system auto-saves every 30 seconds

## File Structure

```
mod/excalidraw/
├── amd/
│   └── src/
│       ├── app.js                  # Main AMD module
│       └── excalidraw-bundle.js    # Excalidraw library bundle
├── classes/
│   ├── local/                      # Local classes
│   └── privacy/                    # Privacy API (GDPR)
│       └── provider.php
├── db/
│   ├── access.php                  # Capabilities
│   └── install.xml                 # Database schema
├── lang/
│   └── en/
│       └── excalidraw.php          # English language strings
├── pix/
│   ├── icon.svg                    # Plugin icon
│   └── monologo.svg                # Monochrome logo
├── styles/
│   └── excalidraw.css              # Excalidraw styles
├── build-entry.js                  # Build entry point
├── index.php                       # Course activity overview
├── lib.php                         # Core Moodle callbacks
├── mod_form.php                    # Activity settings form
├── save.php                        # AJAX save handler
├── version.php                     # Plugin version
└── view.php                        # Main activity view

```

## Database Tables

### excalidraw

Stores activity instances:
- `id` - Primary key
- `course` - Course ID
- `name` - Activity name
- `intro` - Activity description
- `introformat` - Text format
- `grade` - Maximum grade
- `timecreated` - Creation timestamp
- `timemodified` - Modification timestamp

### excalidraw_submissions

Stores user submissions:
- `id` - Primary key
- `excalidrawid` - Activity ID
- `userid` - User ID
- `content` - JSON drawing data
- `grade` - Assigned grade
- `timecreated` - Creation timestamp
- `timemodified` - Modification timestamp

## Capabilities

- `mod/excalidraw:view` - View Excalidraw activities
- `mod/excalidraw:addinstance` - Add new Excalidraw activities
- `mod/excalidraw:submit` - Submit drawings
- `mod/excalidraw:grade` - Grade submissions

## Development

### Building for Development

```bash
# Build packages
yarn build:packages

# Build Moodle bundle
yarn build:moodle
```

### Testing

After making changes to the JavaScript:

1. Rebuild the bundle: `yarn build:moodle`
2. Copy files to your Moodle installation
3. Purge Moodle caches: **Site administration → Development → Purge all caches**
4. Test in your browser

## Troubleshooting

### Plugin Not Appearing

- Ensure the `mod/excalidraw` directory is in the correct location
- Check file permissions (should be readable by web server)
- Visit **Site administration → Notifications** to trigger upgrade

### JavaScript Not Loading

- Clear Moodle caches
- Check browser console for errors
- Verify that `excalidraw-bundle.js` exists in `amd/src/`

### Drawings Not Saving

- Check browser console for AJAX errors
- Verify database tables were created
- Check user capabilities
- Ensure `sesskey` is being passed correctly

## Privacy (GDPR)

The plugin implements Moodle's Privacy API for GDPR compliance:

- User submissions are tracked per user
- Users can export their data
- Users can request deletion of their data
- All personal data is properly tagged

## License

GNU GPL v3 or later

## Credits

Built on [Excalidraw](https://excalidraw.com/) - An open source virtual hand-drawn style whiteboard.

## Support

For issues and feature requests, please use the GitHub issue tracker.
