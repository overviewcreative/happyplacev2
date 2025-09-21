@echo off
echo ========================================
echo Hero-Only Image Processor
echo High-Quality 2500px Hero Images
echo ========================================
echo.

REM Navigate to theme directory
cd /d "C:\Users\pat\Local Sites\happy-placev31\app\public\wp-content\themes\happy-place-theme"

echo 🎨 Settings:
echo    📐 Max dimension: 2500px (long side)
echo    🖼️  Quality: 90%% JPEG, 88%% WebP
echo    📏 No cropping (maintains aspect ratio)
echo    ⚡ Hero images only (faster processing)
echo.

REM Check if Node.js is available
node --version >nul 2>&1
if errorlevel 1 (
    echo ❌ Node.js not found. Please install Node.js first.
    pause
    exit /b 1
)

echo 🔍 Scanning property directories...
echo.

REM Show first few properties found
set /a count=0
for /d %%i in ("C:\Users\pat\Desktop\TPG Listing Photos\*") do (
    set /a count+=1
    if !count! leq 5 echo Found: %%~nxi
)

if %count% gtr 5 echo ... and %count% more properties

echo.
echo 🏠 Processing all properties (hero images only)...
echo This will be much faster than the full processing.
echo.

REM Create batch processor for hero-only
node -e "
const fs = require('fs');
const path = require('path');
const { execSync } = require('child_process');

const baseInputDir = 'C:\\\\Users\\\\pat\\\\Desktop\\\\TPG Listing Photos';
const baseOutputDir = 'C:\\\\Users\\\\pat\\\\Desktop\\\\Hero-Only-Optimized';

async function processAllHero() {
    console.log('🔍 Finding property directories...');

    const entries = fs.readdirSync(baseInputDir, { withFileTypes: true });
    const properties = entries.filter(entry => entry.isDirectory());

    console.log(\`📁 Found \${properties.length} property directories\n\`);

    for (let i = 0; i < properties.length; i++) {
        const property = properties[i];
        const cleanName = property.name.toLowerCase().replace(/[\\\\/:*?\"<>|]/g, '-').replace(/\\s+/g, '-');

        console.log(\`\n🏠 Processing \${i + 1}/\${properties.length}: \${property.name}\`);

        const inputDir = path.join(baseInputDir, property.name);
        const outputDir = path.join(baseOutputDir, cleanName);

        try {
            const command = \`node scripts/hero-only-processor.js --input \"\${inputDir}\" --output \"\${outputDir}\" --property \"\${property.name}\"\`;
            execSync(command, { stdio: 'inherit' });
            console.log(\`   ✅ Completed\`);
        } catch (error) {
            console.error(\`   ❌ Error: \${error.message}\`);
        }
    }

    console.log(\`\n✅ All properties processed!\`);
    console.log(\`📂 Output: \${baseOutputDir}\`);

    try {
        execSync(\`explorer \"\${baseOutputDir}\"\`, { stdio: 'ignore' });
    } catch (e) {}
}

processAllHero();
"

if errorlevel 1 (
    echo.
    echo ❌ Processing failed. Check the error messages above.
    pause
    exit /b 1
)

echo.
echo ========================================
echo ✅ Hero Processing Complete!
echo ========================================
echo.
echo 📂 High-quality hero images saved to:
echo    C:\Users\pat\Desktop\Hero-Only-Optimized
echo.
echo 🎨 Image specifications:
echo    📐 2500px max dimension (aspect ratio maintained)
echo    🖼️  90%% JPEG quality, 88%% WebP quality
echo    📏 No cropping or distortion
echo    ⚡ Perfect for property hero sections
echo.
echo 📋 Next Steps:
echo    1. Review hero images in output folders
echo    2. Upload to WordPress Media Library
echo    3. Use as Featured Images for listings
echo    4. Set as hero images in property galleries
echo.

pause