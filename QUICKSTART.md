# Quick Start Guide - Build & Install

## 🚀 Build in 3 Commands

```bash
yarn install           # Install dependencies (5-10 min)
yarn build:packages    # Build Excalidraw (2-5 min)
yarn build:moodle      # Build plugin (1-2 min)
```

**Total time:** ~8-17 minutes

---

## 📋 Prerequisites

| Tool | Version | Download |
|------|---------|----------|
| Node.js | 18.0+ | https://nodejs.org/ |
| Yarn | 1.22+ | `npm install -g yarn` |

---

## 🛠️ Automated Build

### Linux/Mac:
```bash
./build-plugin.sh
```

### Windows:
```cmd
build-plugin.bat
```

---

## ✅ Verify Build

Check these files exist:

```
mod/excalidraw/amd/src/excalidraw-bundle.js   (~1-2MB)
mod/excalidraw/styles/excalidraw.css          (~50KB)
```

---

## 📦 Install in Moodle

### Method 1: Copy
```bash
cp -r mod/excalidraw /path/to/moodle/mod/
```

### Method 2: Zip
```bash
cd mod
zip -r excalidraw.zip excalidraw/
```
Upload via Moodle admin interface

---

## 🔧 In Moodle

1. Login as **admin**
2. Go to **Site administration** → **Notifications**
3. Click **"Upgrade Moodle database now"**
4. Done! ✅

---

## 🆘 Troubleshooting

| Problem | Solution |
|---------|----------|
| `command not found: node` | Install Node.js from nodejs.org |
| `command not found: yarn` | Run `npm install -g yarn` |
| Build fails | Try `yarn install` again |
| Out of memory | Run `export NODE_OPTIONS="--max-old-space-size=4096"` |

---

## 📚 More Info

- **Detailed build guide:** BUILD_INSTRUCTIONS.md
- **Installation help:** INSTALL.md
- **Usage guide:** README.md

---

## ⚡ That's It!

Your plugin is ready to install in Moodle 5.0.2!
