# Excalidraw Moodle Plugin - Technical Overview

## What This Plugin Does

This plugin transforms Excalidraw into a Moodle activity module, allowing teachers to create drawing activities and students to submit their drawings for grading.

## Key Components

### Core Plugin Files

| File | Purpose |
|------|---------|
| `version.php` | Plugin version, Moodle requirements, maturity level |
| `lib.php` | Moodle callbacks (add/update/delete instances, grading, file serving) |
| `mod_form.php` | Activity configuration form (name, description, grade) |
| `view.php` | Main student/teacher view - loads Excalidraw canvas |
| `index.php` | Course overview page showing all Excalidraw activities |
| `save.php` | AJAX endpoint for saving drawing data |

### Database Schema

#### `mdl_excalidraw` Table
Stores activity instances with:
- Activity name and description
- Maximum grade
- Course association
- Timestamps

#### `mdl_excalidraw_submissions` Table
Stores user submissions with:
- Drawing data (JSON format)
- User and activity association
- Grade (nullable)
- Timestamps

### JavaScript Integration

| File | Purpose |
|------|---------|
| `amd/src/app.js` | AMD module that initializes Excalidraw and handles saving |
| `amd/src/excalidraw-bundle.js` | Bundled Excalidraw library with React and ReactDOM |
| `build-entry.js` | Build entry point that exposes Excalidraw to global scope |

### Build System

| File | Purpose |
|------|---------|
| `scripts/build-moodle-plugin.js` | Build script that bundles Excalidraw for Moodle |
| Root `package.json` | Added `build:moodle` script |

### Privacy & GDPR

| File | Purpose |
|------|---------|
| `classes/privacy/provider.php` | Privacy API implementation for GDPR compliance |

### Localization

| File | Purpose |
|------|---------|
| `lang/en/excalidraw.php` | English language strings |

### Security

| File | Purpose |
|------|---------|
| `db/access.php` | Capability definitions (view, addinstance, submit, grade) |

## Data Flow

### Loading a Drawing

1. User opens activity (`view.php`)
2. PHP queries for existing submission
3. Submission content (JSON) passed to JavaScript
4. AMD module (`app.js`) initializes Excalidraw with data
5. Excalidraw renders the canvas

### Saving a Drawing

1. User clicks "Save" or auto-save triggers
2. JavaScript extracts canvas data from Excalidraw API
3. AJAX POST to `save.php` with JSON data
4. PHP validates session and capability
5. Data saved/updated in `mdl_excalidraw_submissions`
6. Success/error returned to frontend

### Grading (Future Implementation)

1. Teacher clicks "View all submissions"
2. PHP queries all submissions for activity
3. Display table with student names and thumbnails
4. Teacher can open, review, and grade each submission
5. Grades sync to Moodle gradebook via `excalidraw_update_grades()`

## File Serving

Uses Moodle's `pluginfile.php` system:

- File area: `submissions`
- Context: Module context
- Item ID: Submission ID
- Capability check: User owns submission OR has grading capability

## Capabilities System

| Capability | Default Roles | Purpose |
|------------|---------------|---------|
| `mod/excalidraw:view` | All | View activity |
| `mod/excalidraw:addinstance` | Teacher, Manager | Create activities |
| `mod/excalidraw:submit` | Student, Teacher | Submit drawings |
| `mod/excalidraw:grade` | Teacher, Manager | Grade submissions |

## Integration Points

### Gradebook Integration

- `excalidraw_update_grades()` - Updates gradebook
- `excalidraw_grade_item_update()` - Creates/updates grade item
- `excalidraw_get_user_grades()` - Retrieves user grades

### Course Integration

- Appears in "Add activity or resource" picker
- Shows in course outline
- Supports completion tracking (prepared, not yet implemented)

### Privacy API (GDPR)

- `get_metadata()` - Declares what data is stored
- `get_contexts_for_userid()` - Finds user's data
- `export_user_data()` - Exports user's drawings
- `delete_data_for_user()` - Removes user's data

## Security Measures

1. **Capability Checks**: All pages require appropriate capabilities
2. **Session Validation**: `require_sesskey()` on AJAX save
3. **Context Validation**: Files served only to authorized users
4. **JSON Validation**: Drawing data validated before storage
5. **XSS Protection**: Uses Moodle's output functions

## Development Workflow

### Making Changes

1. **Edit source files** in `mod/excalidraw/`
2. **Rebuild** if JavaScript changed: `yarn build:moodle`
3. **Copy** to Moodle: `cp -r mod/excalidraw /path/to/moodle/mod/`
4. **Purge caches** in Moodle
5. **Test** in browser

### Adding New Features

**To add a setting:**
- Edit `mod_form.php` → Add form element
- Edit `lib.php` → Handle in `excalidraw_add_instance()`
- May need database upgrade

**To add a capability:**
- Edit `db/access.php` → Add capability definition
- Add language string
- Increment version → Trigger upgrade

**To add a grading view:**
- Create `view_submissions.php`
- Create `classes/output/grading_table.php`
- Add template in `templates/`
- Add route in activity menu

## Known Limitations

### Current Version

- ✅ Create drawings
- ✅ Save/load drawings
- ✅ Basic grading support (structure ready)
- ❌ Teacher grading interface not yet built
- ❌ Rubric support not implemented
- ❌ Collaboration/real-time not implemented
- ❌ Image export not implemented
- ❌ Accessibility enhancements needed

### Future Enhancements

1. **Teacher grading interface**: View all submissions, inline feedback
2. **Export options**: PNG, SVG, PDF export
3. **Collaboration**: Real-time multi-user editing
4. **Templates**: Provide starter templates
5. **Accessibility**: WCAG 2.1 AA compliance
6. **Mobile app**: Better mobile/tablet support
7. **Version history**: Track changes over time

## Browser Compatibility

### Tested

- ✅ Chrome 90+
- ✅ Firefox 88+
- ✅ Safari 14+
- ✅ Edge 90+

### Requirements

- Modern browser with ES6 support
- Canvas API support
- LocalStorage (for auto-save fallback)

## Performance Considerations

### Bundle Size

- Excalidraw bundle: ~1-2 MB (minified)
- Loads on demand (only on activity view)
- Consider CDN for production

### Database

- JSON storage efficient for drawings
- Index on `(excalidrawid, userid)` for fast lookup
- Consider cleanup of old submissions

### Caching

- Moodle caches JavaScript/CSS
- Drawing data retrieved fresh each time
- Auto-save reduces data loss

## Testing Checklist

- [ ] Install plugin fresh
- [ ] Create activity as teacher
- [ ] Open activity as student
- [ ] Draw something and save
- [ ] Reload page - drawing persists
- [ ] Auto-save works after 30s
- [ ] Update activity settings
- [ ] Delete activity (check cascade)
- [ ] Export user data (Privacy API)
- [ ] Delete user data (Privacy API)

## Support & Resources

- **Moodle Docs**: https://docs.moodle.org/dev/
- **Excalidraw**: https://github.com/excalidraw/excalidraw
- **Plugin Development**: https://moodledev.io/

## License

GNU GPL v3 or later (same as Moodle)
