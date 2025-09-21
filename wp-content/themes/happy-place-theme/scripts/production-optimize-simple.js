#!/usr/bin/env node

/**
 * Simple Production Optimization Script
 * Basic post-build optimizations for production deployment
 */

const fs = require('fs');
const path = require('path');
const { gzipSync } = require('zlib');

const DIST_DIR = path.resolve(__dirname, '../dist');

console.log('🚀 Starting production optimization...');

function createCompressedAssets() {
    console.log('🗜️  Creating compressed assets...');

    const assetDirs = ['css', 'js'];
    let totalFiles = 0;
    let totalSaved = 0;

    for (const dir of assetDirs) {
        const dirPath = path.join(DIST_DIR, dir);
        if (!fs.existsSync(dirPath)) continue;

        const files = fs.readdirSync(dirPath);

        for (const file of files) {
            if (file.endsWith('.gz') || file.endsWith('.br')) continue;

            const filePath = path.join(dirPath, file);
            const stats = fs.statSync(filePath);

            // Only compress files larger than 1KB
            if (stats.size > 1024) {
                const content = fs.readFileSync(filePath);
                const gzipped = gzipSync(content, { level: 9 });

                fs.writeFileSync(filePath + '.gz', gzipped);

                totalFiles++;
                totalSaved += (stats.size - gzipped.length);

                console.log(`  ✓ Compressed ${file} (${formatBytes(stats.size)} → ${formatBytes(gzipped.length)})`);
            }
        }
    }

    console.log(`📊 Compressed ${totalFiles} files, saved ${formatBytes(totalSaved)}`);
}

function createCacheHeaders() {
    console.log('🏠 Creating cache headers configuration...');

    const htaccess = `# Happy Place Theme - Production Cache Headers
# Generated: ${new Date().toISOString()}

<IfModule mod_expires.c>
    ExpiresActive On

    # CSS and JavaScript - 1 year (with cache busting)
    ExpiresByType text/css "access plus 1 year"
    ExpiresByType application/javascript "access plus 1 year"

    # Images - 6 months
    ExpiresByType image/png "access plus 6 months"
    ExpiresByType image/jpg "access plus 6 months"
    ExpiresByType image/jpeg "access plus 6 months"
    ExpiresByType image/webp "access plus 6 months"
</IfModule>

<IfModule mod_deflate.c>
    # Compress text files
    AddOutputFilterByType DEFLATE text/css
    AddOutputFilterByType DEFLATE application/javascript
    AddOutputFilterByType DEFLATE text/html
</IfModule>

<IfModule mod_headers.c>
    # Cache control for assets with hashes
    <FilesMatch "\\.(css|js)$">
        Header set Cache-Control "public, max-age=31536000, immutable"
    </FilesMatch>
</IfModule>`;

    fs.writeFileSync(path.join(DIST_DIR, '.htaccess'), htaccess);
    console.log('  ✓ Created .htaccess with cache headers');
}

function generateAssetManifest() {
    console.log('📋 Generating asset manifest...');

    const manifest = {};
    const assetDirs = ['css', 'js'];

    for (const dir of assetDirs) {
        const dirPath = path.join(DIST_DIR, dir);
        if (!fs.existsSync(dirPath)) continue;

        const files = fs.readdirSync(dirPath);

        for (const file of files) {
            if (file.endsWith('.gz') || file.endsWith('.br')) continue;

            const filePath = path.join(dirPath, file);
            const stats = fs.statSync(filePath);

            manifest[file] = {
                size: stats.size,
                path: `/${dir}/${file}`,
                mtime: stats.mtime.toISOString(),
                gzip: fs.existsSync(filePath + '.gz')
            };
        }
    }

    fs.writeFileSync(
        path.join(DIST_DIR, 'asset-manifest.json'),
        JSON.stringify(manifest, null, 2)
    );

    console.log(`  ✓ Created asset manifest with ${Object.keys(manifest).length} assets`);
}

function formatBytes(bytes) {
    if (bytes === 0) return '0 B';
    const k = 1024;
    const sizes = ['B', 'KB', 'MB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(1)) + ' ' + sizes[i];
}

// Main execution
try {
    if (!fs.existsSync(DIST_DIR)) {
        throw new Error('Dist directory not found. Run "npm run build" first.');
    }

    createCompressedAssets();
    createCacheHeaders();
    generateAssetManifest();

    console.log('✅ Production optimization complete!');
} catch (error) {
    console.error('❌ Optimization failed:', error.message);
    process.exit(1);
}