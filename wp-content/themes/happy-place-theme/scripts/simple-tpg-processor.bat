@echo off
echo ========================================
echo Simple TPG Photo Processor
echo ========================================

cd /d "C:\Users\pat\Local Sites\happy-placev31\app\public\wp-content\themes\happy-place-theme"

echo.
echo 🔄 Processing first property as test...
node scripts/hero-only-processor.js --input "C:\Users\pat\Desktop\TPG Listing Photos\115 Chestnut St Milton DE" --output "C:\Users\pat\Desktop\TPG-Output-Test" --property "115 Chestnut St Milton DE"

echo.
echo ✅ Test complete! Check C:\Users\pat\Desktop\TPG-Output-Test
echo.
echo Would you like to process more properties? (Y/N)
set /p choice=

if /i "%choice%"=="Y" (
    echo.
    echo 🔄 Processing second property...
    node scripts/hero-only-processor.js --input "C:\Users\pat\Desktop\TPG Listing Photos\1113 Kayla Ln Townsend DE" --output "C:\Users\pat\Desktop\TPG-Output-Test\1113-kayla-ln-townsend-de" --property "1113 Kayla Ln Townsend DE"

    echo.
    echo ✅ Two properties done! Check the output folder.
)

echo.
echo 📂 Opening output folder...
explorer "C:\Users\pat\Desktop\TPG-Output-Test"

pause