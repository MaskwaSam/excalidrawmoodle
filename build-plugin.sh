#!/bin/bash
# Build script for Excalidraw Moodle Plugin
# Run this on your local machine

set -e  # Exit on error

echo "====================================="
echo "Excalidraw Moodle Plugin Builder"
echo "====================================="
echo ""

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Check Node.js
echo "Checking Node.js..."
if ! command -v node &> /dev/null; then
    echo -e "${RED}Error: Node.js is not installed${NC}"
    echo "Please install Node.js 18.0 or higher from https://nodejs.org/"
    exit 1
fi

NODE_VERSION=$(node --version)
echo -e "${GREEN}✓ Node.js found: $NODE_VERSION${NC}"

# Check Yarn
echo "Checking Yarn..."
if ! command -v yarn &> /dev/null; then
    echo -e "${RED}Error: Yarn is not installed${NC}"
    echo "Install with: npm install -g yarn"
    exit 1
fi

YARN_VERSION=$(yarn --version)
echo -e "${GREEN}✓ Yarn found: $YARN_VERSION${NC}"
echo ""

# Step 1: Install dependencies
echo "====================================="
echo "Step 1: Installing dependencies..."
echo "====================================="
echo "This may take several minutes..."
echo ""

if [ ! -d "node_modules" ]; then
    yarn install
    if [ $? -ne 0 ]; then
        echo -e "${RED}Failed to install dependencies${NC}"
        exit 1
    fi
else
    echo -e "${YELLOW}Dependencies already installed, skipping...${NC}"
fi

echo -e "${GREEN}✓ Dependencies installed${NC}"
echo ""

# Step 2: Build packages
echo "====================================="
echo "Step 2: Building Excalidraw packages..."
echo "====================================="
echo "Building common, math, element, and excalidraw packages..."
echo ""

yarn build:packages
if [ $? -ne 0 ]; then
    echo -e "${RED}Failed to build packages${NC}"
    exit 1
fi

echo -e "${GREEN}✓ Packages built${NC}"
echo ""

# Step 3: Build Moodle plugin
echo "====================================="
echo "Step 3: Building Moodle plugin bundle..."
echo "====================================="
echo ""

node ./scripts/build-moodle-plugin.js
if [ $? -ne 0 ]; then
    echo -e "${RED}Failed to build Moodle plugin${NC}"
    exit 1
fi

echo -e "${GREEN}✓ Moodle plugin built${NC}"
echo ""

# Step 4: Verify output
echo "====================================="
echo "Step 4: Verifying build output..."
echo "====================================="
echo ""

# Check for required files
REQUIRED_FILES=(
    "mod/excalidraw/amd/src/excalidraw-bundle.js"
    "mod/excalidraw/styles/excalidraw.css"
    "mod/excalidraw/version.php"
    "mod/excalidraw/lib.php"
)

ALL_EXIST=true
for file in "${REQUIRED_FILES[@]}"; do
    if [ -f "$file" ]; then
        echo -e "${GREEN}✓ $file${NC}"
    else
        echo -e "${RED}✗ $file (MISSING)${NC}"
        ALL_EXIST=false
    fi
done

if [ "$ALL_EXIST" = false ]; then
    echo ""
    echo -e "${RED}Some required files are missing!${NC}"
    exit 1
fi

# Get file sizes
echo ""
echo "File sizes:"
if [ -f "mod/excalidraw/amd/src/excalidraw-bundle.js" ]; then
    BUNDLE_SIZE=$(du -h mod/excalidraw/amd/src/excalidraw-bundle.js | cut -f1)
    echo "  Bundle JS: $BUNDLE_SIZE"
fi
if [ -f "mod/excalidraw/styles/excalidraw.css" ]; then
    CSS_SIZE=$(du -h mod/excalidraw/styles/excalidraw.css | cut -f1)
    echo "  CSS: $CSS_SIZE"
fi

echo ""
echo "====================================="
echo -e "${GREEN}BUILD SUCCESSFUL!${NC}"
echo "====================================="
echo ""
echo "Next steps:"
echo "1. Copy the plugin to your Moodle installation:"
echo "   cp -r mod/excalidraw /path/to/moodle/mod/"
echo ""
echo "2. Or create a zip file:"
echo "   cd mod && zip -r excalidraw.zip excalidraw/"
echo ""
echo "3. Install in Moodle:"
echo "   - Log in as admin"
echo "   - Go to Site administration → Notifications"
echo "   - Click 'Upgrade Moodle database now'"
echo ""
echo "Plugin location: $(pwd)/mod/excalidraw"
echo ""
