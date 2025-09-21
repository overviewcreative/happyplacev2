@echo off
echo ========================================
echo Real Estate Batch Image Processor
echo Processing ALL Properties
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

echo 🔍 Scanning property directories...
echo.

REM Check how many properties we found
for /d %%i in ("C:\Users\pat\Desktop\Web Sized Listing Photos\*") do (
    echo Found: %%~nxi
)

echo.
echo 🏠 Processing all property folders...
echo This may take several minutes depending on the number of photos.
echo.

REM Process all properties
node scripts/process-all-properties.js

if errorlevel 1 (
    echo.
    echo ❌ Processing failed. Check the error messages above.
    pause
    exit /b 1
)

echo.
echo ========================================
echo ✅ Batch Processing Complete!
echo ========================================
echo.
echo 📂 All optimized images saved to:
echo    C:\Users\pat\Desktop\Optimized-Ready-for-Upload
echo.
echo 📋 Next Steps:
echo    1. Review individual property folders
echo    2. Check processing reports in each folder
echo    3. Upload optimized images to WordPress
echo    4. Update property listings
echo.

pause