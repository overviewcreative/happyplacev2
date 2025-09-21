@echo off
echo ========================================
echo TPG Listing Photos - Hero Processor
echo High-Quality 2500px Hero Images
echo ========================================
echo.

REM Navigate to theme directory
cd /d "C:\Users\pat\Local Sites\happy-placev31\app\public\wp-content\themes\happy-place-theme"

echo 🎨 Settings:
echo    📐 Max dimension: 2500px (long side)
echo    🖼️  Quality: 90%% JPEG, 88%% WebP
echo    📏 No cropping (maintains aspect ratio)
echo    🏷️  SEO naming with town and state
echo    ⚡ Hero images only (faster processing)
echo.

REM Check if Node.js is available
node --version >nul 2>&1
if errorlevel 1 (
    echo ❌ Node.js not found. Please install Node.js first.
    pause
    exit /b 1
)

echo 🔍 Scanning TPG Listing Photos directory...
echo.

REM Check if the directory exists
if not exist "C:\Users\pat\Desktop\TPG Listing Photos" (
    echo ❌ Directory not found: C:\Users\pat\Desktop\TPG Listing Photos
    echo.
    echo Please check that the path is correct.
    pause
    exit /b 1
)

REM Show first few properties found
set /a count=0
for /d %%i in ("C:\Users\pat\Desktop\TPG Listing Photos\*") do (
    set /a count+=1
    if !count! leq 5 echo Found: %%~nxi
)

if %count% gtr 5 echo ... and %count% more properties

echo.
echo 🏠 Processing TPG Listing Photos (hero images only)...
echo This will create optimized hero images for all properties.
echo.

REM Process all TPG properties
node -e "
const fs = require('fs');
const path = require('path');
const { execSync } = require('child_process');

const baseInputDir = 'C:\\\\Users\\\\pat\\\\Desktop\\\\TPG Listing Photos';
const baseOutputDir = 'C:\\\\Users\\\\pat\\\\Desktop\\\\TPG-Hero-Optimized';

async function processAllTPGHero() {
    console.log('🔍 Finding TPG property directories...');

    try {
        const entries = fs.readdirSync(baseInputDir, { withFileTypes: true });
        const properties = entries.filter(entry => entry.isDirectory());

        console.log(\`📁 Found \${properties.length} TPG property directories\n\`);

        if (properties.length === 0) {
            console.log('❌ No property directories found in TPG Listing Photos');
            return;
        }

        for (let i = 0; i < properties.length; i++) {
            const property = properties[i];
            const cleanName = property.name.toLowerCase()
                .replace(/[\\\\/:*?\"<>|]/g, '-')
                .replace(/\\s+/g, '-')
                .replace(/-+/g, '-')
                .trim('-');

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

        console.log(\`\n✅ All TPG properties processed!\`);
        console.log(\`📂 Output: \${baseOutputDir}\`);

        try {
            execSync(\`explorer \"\${baseOutputDir}\"\`, { stdio: 'ignore' });
        } catch (e) {
            console.log('📂 Please manually open:', baseOutputDir);
        }
    } catch (error) {
        console.error('❌ Error accessing TPG directory:', error.message);
        console.log('Please check that the directory exists and is accessible.');
    }
}

processAllTPGHero();
"

if errorlevel 1 (
    echo.
    echo ❌ Processing failed. Check the error messages above.
    pause
    exit /b 1
)

echo.
echo ========================================
echo ✅ TPG Hero Processing Complete!
echo ========================================
echo.
echo 📂 High-quality hero images saved to:
echo    C:\Users\pat\Desktop\TPG-Hero-Optimized
echo.
echo 🎨 Image specifications:
echo    📐 2500px max dimension (aspect ratio maintained)
echo    🖼️  90%% JPEG quality, 88%% WebP quality
echo    📏 No cropping or distortion
echo    🏷️  SEO-friendly naming with location
echo    ⚡ Perfect for property hero sections
echo.
echo 📋 Next Steps:
echo    1. Review hero images in output folders
echo    2. Upload to WordPress Media Library
echo    3. Use as Featured Images for listings
echo    4. Set as hero images in property galleries
echo.

pause