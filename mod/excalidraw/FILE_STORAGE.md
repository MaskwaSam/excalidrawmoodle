# File Storage Implementation - Excalidraw Moodle Plugin

## Overview

The Excalidraw Moodle plugin uses **dual storage** for drawing data:
1. **JSON in database** - For fast loading and quick access
2. **Files in Moodle File API** - For proper file management, backups, and exports

## Storage Architecture

### Database Storage (Primary)
**Table:** `mdl_excalidraw_submissions`
**Field:** `content` (TEXT)
**Purpose:** Quick loading when user opens the activity

The JSON contains:
- Drawing elements (shapes, arrows, text, etc.)
- App state (colors, tool settings, background)

### File Storage (Secondary)
**Component:** `mod_excalidraw`
**File Area:** `submissions`
**Item ID:** Submission ID from `mdl_excalidraw_submissions`
**Purpose:** Proper file management, backups, exports

Files are stored with naming convention:
```
drawing_[userid]_[timestamp].excalidraw
```

## How It Works

### Saving a Drawing

**File:** `mod/excalidraw/save.php`

1. User clicks "Save" or auto-save triggers
2. JavaScript sends JSON data via AJAX
3. PHP validates the JSON structure
4. **Dual Save Process:**
   - **Step A:** Save to File Storage
     ```php
     $fs = get_file_storage();
     $file = $fs->create_file_from_string($fileinfo, $content);
     ```
   - **Step B:** Save JSON to Database
     ```php
     $submission->content = $content;
     $DB->update_record('excalidraw_submissions', $submission);
     ```
5. Returns success with file ID

### Loading a Drawing

**File:** `mod/excalidraw/view.php`

1. User opens activity
2. PHP loads JSON from database (fast)
3. JavaScript initializes Excalidraw with data
4. File in storage available for export/backup

## File Management Functions

### Get Latest File
```php
$file = excalidraw_get_submission_file($contextid, $submissionid);
if ($file) {
    $content = $file->get_content();
}
```

### Get All Files (Version History)
```php
$files = excalidraw_get_submission_files($contextid, $submissionid);
foreach ($files as $file) {
    echo $file->get_filename() . ' - ' . $file->get_timemodified();
}
```

### Serve File to User
Files are automatically served via `excalidraw_pluginfile()` function using Moodle's pluginfile.php system.

**URL Format:**
```
/pluginfile.php/{contextid}/mod_excalidraw/submissions/{submissionid}/{filename}
```

## File Permissions

### Who Can Access Files?

**Students:**
- Can access their own submission files
- Cannot access other students' files

**Teachers/Graders:**
- Can access all submission files
- Uses `mod/excalidraw:grade` capability

**Implementation:**
```php
if ($submission->userid != $USER->id && !has_capability('mod/excalidraw:grade', $context)) {
    return false;
}
```

## File Lifecycle

### Creation
- File created when user first saves
- Stored in module context with submission ID as itemid

### Updates
- Old file deleted when user saves again
- New file created with updated timestamp
- Database JSON always reflects latest version

### Deletion
- Files deleted when:
  - Activity instance is deleted
  - Submission is manually deleted
  - Course is deleted (via Moodle cascade)

**Implementation in `excalidraw_delete_instance()`:**
```php
$fs->delete_area_files($context->id, 'mod_excalidraw', 'submissions', $submission->id);
```

## Storage Benefits

### Why Both?

| Storage Method | Benefit |
|----------------|---------|
| **Database JSON** | ⚡ Fast loading, no file I/O needed |
| **File Storage** | 📦 Proper backups, exports, file management |

### Advantages

1. **Performance:** Loading from database is faster than file I/O
2. **Backups:** Moodle backup system includes files automatically
3. **Exports:** Files can be downloaded as .excalidraw format
4. **History:** Can track file versions (future enhancement)
5. **Privacy:** GDPR exports include files via Privacy API

## File Areas

The plugin uses two file areas:

### 1. `submissions` (Main storage)
- **Purpose:** Student drawing files
- **Access:** Owner or grader
- **Lifecycle:** Tied to submission

### 2. `intro` (Activity description)
- **Purpose:** Images/files in activity description
- **Access:** All enrolled users
- **Lifecycle:** Tied to activity instance

## Future Enhancements

### Version History
Track all file versions instead of deleting old ones:
- Keep old files for rollback
- Show timeline of changes
- Compare versions

### Export Formats
Export drawings in multiple formats:
- PNG (raster image)
- SVG (vector graphic)
- PDF (printable document)
- .excalidraw (native format)

### File Quotas
Implement storage limits:
- Per-user quota
- Per-course quota
- Automatic cleanup of old files

### Thumbnails
Generate thumbnails for quick preview:
- Save PNG thumbnail on each save
- Display in submission list
- Faster than loading full drawing

## Privacy & GDPR

The Privacy API (`classes/privacy/provider.php`) handles:
- **Export:** Includes both database content and files
- **Delete:** Removes both database records and files
- **Anonymize:** Can replace user-specific data

## Troubleshooting

### Files Not Saving
1. Check file permissions on moodledata
2. Verify context ID is correct
3. Check PHP error logs for file_storage errors

### Files Not Loading
1. Verify pluginfile function is working
2. Check capability permissions
3. Ensure file exists in database

### Database Out of Sync with Files
If JSON in database doesn't match file:
- Database is the source of truth for loading
- File should be regenerated on next save
- Use helper function to retrieve latest file

## Testing Checklist

- [ ] Save drawing - verify file created
- [ ] Reload page - verify drawing loads
- [ ] Save again - verify old file deleted
- [ ] Check moodledata - verify .excalidraw file exists
- [ ] Delete activity - verify files deleted
- [ ] Privacy export - verify files included
- [ ] Download file directly - verify content correct

## References

- **Moodle File API:** https://docs.moodle.org/dev/File_API
- **File Storage:** https://moodledev.io/docs/5.0/apis/subsystems/files
- **Privacy API:** https://docs.moodle.org/dev/Privacy_API
