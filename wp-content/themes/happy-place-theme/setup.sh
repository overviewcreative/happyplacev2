#!/bin/bash

echo "🚀 Setting up Happy Place Theme build system..."

# Clean any existing corrupted installations
echo "🧹 Cleaning old files..."
rm -rf node_modules
rm -f package-lock.json
rm -f .package-lock.json

# Clear npm cache
echo "🗑️ Clearing npm cache..."
npm cache clean --force

# Create required directories if they don't exist
echo "📁 Creating directory structure..."
mkdir -p src/css
mkdir -p src/js
mkdir -p assets/css/framework
mkdir -p assets/js
mkdir -p scripts
mkdir -p dist

# Create placeholder files if they don't exist
if [ ! -f "src/js/main.js" ]; then
    echo "// Main JavaScript file" > src/js/main.js
    echo "✅ Created src/js/main.js"
fi

if [ ! -f "assets/js/main.js" ]; then
    echo "// Main JavaScript file" > assets/js/main.js
    echo "✅ Created assets/js/main.js"
fi

# Create basic CSS entry files if missing
if [ ! -f "src/css/core.css" ]; then
    echo "/* Core styles - imports from framework */" > src/css/core.css
    echo "✅ Created src/css/core.css"
fi

if [ ! -f "assets/css/framework/index.css" ]; then
    echo "/* Framework index */" > assets/css/framework/index.css
    echo "✅ Created assets/css/framework/index.css"
fi

# Install dependencies
echo "📦 Installing npm packages (this may take 2-3 minutes)..."
npm install

# Check if installation was successful
if [ -f "node_modules/.bin/vite" ]; then
    echo "✅ Installation successful!"
    echo ""
    echo "📊 Package count:"
    ls node_modules | wc -l
    echo ""
    echo "🎯 Next steps:"
    echo "  1. Run 'npm run build' to build your assets"
    echo "  2. Run 'npm run dev' for development mode"
    echo "  3. Run 'npm run watch' for auto-rebuild on changes"
else
    echo "❌ Installation failed. Please try:"
    echo "  1. Run this script with admin privileges"
    echo "  2. Temporarily disable antivirus"
    echo "  3. Use Local's Site Shell instead"
fi