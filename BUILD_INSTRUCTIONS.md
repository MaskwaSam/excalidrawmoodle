# Build Instructions - Excalidraw Moodle Plugin

## Prerequisites

Before building, ensure you have:

✅ **Node.js** 18.0 or higher - [Download here](https://nodejs.org/)
✅ **Yarn** package manager - Install with: `npm install -g yarn`
✅ **Git** (optional) - For cloning the repository

---

## Quick Start

### On Linux/Mac:

```bash
# Navigate to plugin directory
cd excalidrawmoodle

# Run build script
./build-plugin.sh
```

### On Windows:

```cmd
# Navigate to plugin directory
cd excalidrawmoodle

# Run build script
build-plugin.bat
```

---

## Manual Build Steps

If you prefer to run commands manually:

### Step 1: Install Dependencies

```bash
yarn install
```

This downloads all required npm packages (~500MB). May take 5-10 minutes.

### Step 2: Build Excalidraw Packages

```bash
yarn build:packages
```

This builds the core Excalidraw libraries. Takes 2-5 minutes.

### Step 3: Build Moodle Plugin Bundle

```bash
yarn build:moodle
```

This creates the Moodle-specific bundle. Takes 1-2 minutes.

### Step 4: Verify Output

Check that these files were created:

```bash
ls mod/excalidraw/amd/src/excalidraw-bundle.js
ls mod/excalidraw/styles/excalidraw.css
```

Both files should exist. Bundle size should be ~1-2MB.

---

## What Gets Built

### Input Files
- `packages/excalidraw/` - Excalidraw source code
- `mod/excalidraw/build-entry.js` - Bundle entry point
- `scripts/build-moodle-plugin.js` - Build script

### Output Files
- `mod/excalidraw/amd/src/excalidraw-bundle.js` - JavaScript bundle (~1-2MB)
- `mod/excalidraw/styles/excalidraw.css` - Styles (~50KB)
- Source maps for debugging

---

## Installation After Building

### Option 1: Copy Directly

```bash
# Copy plugin to Moodle
cp -r mod/excalidraw /path/to/moodle/mod/

# Set permissions (Linux/Mac)
chown -R www-data:www-data /path/to/moodle/mod/excalidraw
chmod -R 755 /path/to/moodle/mod/excalidraw
```

### Option 2: Create Zip File

```bash
# Create zip
cd mod
zip -r excalidraw.zip excalidraw/

# Upload via Moodle:
# Site administration → Plugins → Install plugins
```

### Option 3: Create Tar Archive

```bash
# Create tar.gz
cd mod
tar -czf excalidraw.tar.gz excalidraw/
```

---

## Moodle Installation

1. **Upload/Copy** plugin to `moodle/mod/excalidraw`

2. **Log in** to Moodle as administrator

3. **Navigate** to **Site administration → Notifications**

4. Moodle detects the new plugin automatically

5. **Click** "Upgrade Moodle database now"

6. **Verify** installation:
   - Go to **Site administration → Plugins → Activity modules → Manage activities**
   - "Excalidraw" should be listed and enabled

---

## Build Troubleshooting

### "command not found: node"

**Problem:** Node.js not installed or not in PATH

**Solution:**
- Download from https://nodejs.org/
- Verify: `node --version`
- Should show v18.0.0 or higher

### "command not found: yarn"

**Problem:** Yarn not installed

**Solution:**
```bash
npm install -g yarn
yarn --version
```

### "error: EACCES: permission denied"

**Problem:** Permission issues during install

**Solution (Linux/Mac):**
```bash
# Fix npm permissions
sudo chown -R $USER ~/.npm
sudo chown -R $USER /usr/local/lib/node_modules
```

**Solution (Windows):**
- Run Command Prompt as Administrator

### Network timeout during install

**Problem:** Slow or unstable internet

**Solution:**
```bash
# Increase timeout
yarn install --network-timeout 100000

# Or use offline mirror (if you have yarn.lock)
yarn install --offline
```

### Build fails with "Cannot find module"

**Problem:** Incomplete dependency installation

**Solution:**
```bash
# Clean and reinstall
rm -rf node_modules
yarn cache clean
yarn install
```

### "Out of memory" error

**Problem:** Node.js runs out of heap memory

**Solution:**
```bash
# Increase Node.js memory
export NODE_OPTIONS="--max-old-space-size=4096"
yarn build:moodle
```

### CSS file not created

**Problem:** Excalidraw packages not built first

**Solution:**
```bash
# Build packages before Moodle bundle
yarn build:packages
yarn build:moodle
```

### Bundle size is 0 bytes or tiny

**Problem:** Build failed silently

**Solution:**
```bash
# Check for errors
yarn build:moodle 2>&1 | tee build.log
cat build.log
```

---

## Build on Different Platforms

### Ubuntu/Debian

```bash
# Install Node.js
curl -fsSL https://deb.nodesource.com/setup_20.x | sudo -E bash -
sudo apt-get install -y nodejs

# Install Yarn
npm install -g yarn

# Build
./build-plugin.sh
```

### macOS

```bash
# Install using Homebrew
brew install node
brew install yarn

# Build
./build-plugin.sh
```

### Windows (PowerShell)

```powershell
# Install using Chocolatey
choco install nodejs
choco install yarn

# Build
.\build-plugin.bat
```

### Windows (WSL)

```bash
# Use Linux instructions inside WSL
./build-plugin.sh
```

---

## Development Build vs Production Build

### This Build = Production

The build creates **production-ready** bundles:
- Minified JavaScript (smaller file size)
- Optimized for performance
- Source maps for debugging included

### For Development

If you want to modify Excalidraw code:

```bash
# Development build (faster, not minified)
yarn build:packages --mode development
yarn build:moodle
```

---

## Incremental Builds

After making changes to plugin code (not Excalidraw):

```bash
# Only rebuild Moodle bundle (fast)
yarn build:moodle
```

After making changes to Excalidraw packages:

```bash
# Rebuild everything
yarn build:packages
yarn build:moodle
```

---

## Build Time Estimates

| Step | Time | Size |
|------|------|------|
| yarn install | 5-10 min | ~500MB |
| build:packages | 2-5 min | ~100MB |
| build:moodle | 1-2 min | ~2MB |
| **Total** | **8-17 min** | **~600MB** |

*Times vary based on CPU speed and disk speed*

---

## Verifying Build Success

Run these commands to verify:

```bash
# Check bundle exists and size
ls -lh mod/excalidraw/amd/src/excalidraw-bundle.js
# Should be ~1-2MB

# Check CSS exists
ls -lh mod/excalidraw/styles/excalidraw.css
# Should be ~50KB

# Check plugin structure
ls mod/excalidraw/
# Should see: amd/ classes/ db/ lang/ pix/ styles/ *.php

# Count files
find mod/excalidraw -type f | wc -l
# Should be 20+ files
```

---

## Clean Build (Start Fresh)

If you encounter issues, try a clean build:

```bash
# Remove all built files
yarn rm:build

# Remove dependencies
rm -rf node_modules

# Start fresh
yarn install
yarn build:packages
yarn build:moodle
```

---

## Building on Server (No GUI)

For headless servers:

```bash
# Set environment variables
export CI=true
export NODE_ENV=production

# Build without interaction
yarn install --non-interactive
yarn build:packages --non-interactive
yarn build:moodle
```

---

## Next Steps After Building

1. ✅ Build completed successfully
2. 📦 Plugin ready in `mod/excalidraw/`
3. 📋 See **INSTALL.md** for Moodle installation
4. 📚 See **README.md** for usage instructions
5. 🔧 See **PLUGIN_OVERVIEW.md** for technical details

---

## Getting Help

**Build fails?**
1. Check error messages carefully
2. Review troubleshooting section above
3. Check Node.js version: `node --version` (need 18+)
4. Check Yarn version: `yarn --version`
5. Try clean build (see above)

**Still stuck?**
- Check `build.log` if you created one
- Check Node.js error messages
- Verify disk space: `df -h`
- Check file permissions

---

## Technical Details

### What build:packages Does

```bash
# Builds in order:
1. packages/common      # Common utilities
2. packages/math        # Math utilities
3. packages/element     # Element handling
4. packages/excalidraw  # Main Excalidraw library
```

Each creates a `dist/` folder with compiled output.

### What build:moodle Does

```javascript
// Uses esbuild to:
1. Bundle React + ReactDOM + Excalidraw
2. Create IIFE (Immediately Invoked Function Expression)
3. Expose globals: window.React, window.ReactDOM, window.ExcalidrawLib
4. Output: mod/excalidraw/amd/src/excalidraw-bundle.js
5. Copy CSS from packages/excalidraw/dist/prod/index.css
```

---

## Success Checklist

- [ ] Node.js 18+ installed
- [ ] Yarn installed
- [ ] Dependencies installed (yarn install)
- [ ] Packages built (yarn build:packages)
- [ ] Plugin built (yarn build:moodle)
- [ ] Bundle file exists (~1-2MB)
- [ ] CSS file exists (~50KB)
- [ ] Ready to install in Moodle!

---

**You're ready to install the plugin in Moodle!** 🎉
