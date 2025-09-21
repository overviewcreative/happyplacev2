@echo off
setlocal enabledelayedexpansion
echo ========================================
echo Process ALL TPG Properties
echo Maintains folder structure
echo ========================================

cd /d "C:\Users\pat\Local Sites\happy-placev31\app\public\wp-content\themes\happy-place-theme"

set "INPUT_DIR=C:\Users\pat\Desktop\TPG Listing Photos"
set "OUTPUT_DIR=C:\Users\pat\Desktop\TPG-All-Optimized"

echo 🔍 Scanning TPG directory for properties...
echo.

REM Count properties first
set /a count=0
for /d %%i in ("%INPUT_DIR%\*") do (
    set /a count+=1
)

echo 📁 Found %count% properties to process
echo 📂 Output directory: %OUTPUT_DIR%
echo.

set /a processed=0
for /d %%i in ("%INPUT_DIR%\*") do (
    set /a processed+=1

    REM Get property name
    set "PROPERTY=%%~nxi"

    REM Create clean folder name (replace spaces with dashes, etc.)
    set "CLEAN_NAME=!PROPERTY: =-!"
    set "CLEAN_NAME=!CLEAN_NAME:\=-!"
    set "CLEAN_NAME=!CLEAN_NAME:/=-!"

    echo 🏠 Processing !processed!/%count%: !PROPERTY!
    echo    📂 Creating: %OUTPUT_DIR%\!CLEAN_NAME!

    REM Process this property
    node scripts/hero-only-processor.js --input "%%i" --output "%OUTPUT_DIR%\!CLEAN_NAME!" --property "!PROPERTY!"

    if errorlevel 1 (
        echo    ❌ Error processing !PROPERTY!
    ) else (
        echo    ✅ Completed !PROPERTY!
    )
    echo.
)

echo.
echo ========================================
echo ✅ All Properties Processed!
echo ========================================
echo.
echo 📊 Summary:
echo    🏠 Properties processed: %count%
echo    📂 Output location: %OUTPUT_DIR%
echo    🎨 Each property has its own folder with hero/ subfolder
echo.
echo 📁 Folder structure:
echo    TPG-All-Optimized\
echo    ├── 115-chestnut-st-milton-de\
echo    │   ├── hero\
echo    │   ├── duplicates\
echo    │   └── hero-processing-report.json
echo    ├── 1113-kayla-ln-townsend-de\
echo    │   ├── hero\
echo    │   └── ...
echo    └── ...
echo.
echo 📂 Opening output directory...
explorer "%OUTPUT_DIR%"

pause