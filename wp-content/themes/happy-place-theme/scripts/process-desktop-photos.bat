@echo off
echo ========================================
echo Real Estate Image Processor
echo Processing Desktop Photos
echo ========================================
echo.

REM Navigate to theme directory
cd /d "C:\Users\pat\Local Sites\happy-placev31\app\public\wp-content\themes\happy-place-theme"

REM Check if Node.js is available
node --version >nul 2>&1
if errorlevel 1 (
    echo ❌ Node.js not found. Please install Node.js first.
    pause
    exit /b 1
)

REM Check if Sharp is installed
node -e "require('sharp')" >nul 2>&1
if errorlevel 1 (
    echo 📦 Installing image processing dependencies...
    npm install sharp chalk ora
    if errorlevel 1 (
        echo ❌ Failed to install dependencies
        pause
        exit /b 1
    )
)

echo 🏠 Processing your desktop photos...
echo.

REM Process the desktop photos
node scripts/local-image-processor.js ^
  --input "C:\Users\pat\Desktop\Web Sized Listing Photos" ^
  --output "C:\Users\pat\Desktop\Optimized-Ready-for-Upload" ^
  --property "Desktop Photo Collection"

if errorlevel 1 (
    echo.
    echo ❌ Processing failed. Check the error messages above.
    pause
    exit /b 1
)

echo.
echo ========================================
echo ✅ Processing Complete!
echo ========================================
echo.
echo 📂 Optimized images saved to:
echo    C:\Users\pat\Desktop\Optimized-Ready-for-Upload
echo.
echo 📋 Next Steps:
echo    1. Review the processing report
echo    2. Check duplicates folder for any duplicates found
echo    3. Upload optimized images to WordPress
echo    4. Read UPLOAD-INSTRUCTIONS.md for details
echo.
echo 💾 Opening output folder...
explorer "C:\Users\pat\Desktop\Optimized-Ready-for-Upload"

pause