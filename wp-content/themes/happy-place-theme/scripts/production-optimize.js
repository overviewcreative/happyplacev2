#!/usr/bin/env node

/**
 * Production Optimization Script
 * Advanced post-build optimizations for production deployment
 *
 * Usage: npm run optimize:production
 */

const fs = require('fs');
const path = require('path');
const { gzipSync, brotliCompressSync } = require('zlib');

// Simple console colors without external dependencies
const colors = {
    blue: (text) => `\x1b[34m${text}\x1b[0m`,
    green: (text) => `\x1b[32m${text}\x1b[0m`,
    yellow: (text) => `\x1b[33m${text}\x1b[0m`,
    red: (text) => `\x1b[31m${text}\x1b[0m`,
    gray: (text) => `\x1b[90m${text}\x1b[0m`
};

const DIST_DIR = path.resolve(__dirname, '../dist');
const OPTIMIZATION_CONFIG = {
    css: {
        minify: true,
        removeComments: true,
        removeDuplicates: true
    },
    js: {
        removeConsole: true,
        removeDebugger: true,
        mangle: true
    },
    compression: {
        gzip: true,
        brotli: true,
        threshold: 1024 // Only compress files larger than 1KB
    }
};

async function optimizeProduction() {
    console.log(colors.blue('🚀 Starting production optimization...'));

    try {
        // Check if dist directory exists
        if (!fs.existsSync(DIST_DIR)) {
            throw new Error('Dist directory not found. Run "npm run build" first.');
        }

        await Promise.all([
            optimizeCSS(),
            optimizeJS(),
            createCompressedAssets(),
            generateAssetManifest(),
            createCacheHeaders()
        ]);

        console.log(colors.green('✅ Production optimization complete!'));
        await showOptimizationStats();

    } catch (error) {
        console.error(colors.red('❌ Optimization failed:'), error.message);
        process.exit(1);
    }
}

async function optimizeCSS() {
    console.log(colors.yellow('📐 Optimizing CSS files...'));

    const cssDir = path.join(DIST_DIR, 'css');
    if (!fs.existsSync(cssDir)) return;

    const cssFiles = fs.readdirSync(cssDir);

    for (const file of cssFiles) {
        if (file.endsWith('.css')) {
            const filePath = path.join(cssDir, file);
            let content = fs.readFileSync(filePath, 'utf8');

            // Additional CSS optimizations
            if (OPTIMIZATION_CONFIG.css.removeComments) {
                content = content.replace(/\/\*[\s\S]*?\*\//g, '');
            }

            if (OPTIMIZATION_CONFIG.css.removeDuplicates) {
                // Remove duplicate property declarations
                content = removeDuplicateCSS(content);
            }

            // Further minification
            content = content
                .replace(/\s+/g, ' ')
                .replace(/;\s*}/g, '}')
                .replace(/,\s+/g, ',')
                .trim();

            fs.writeFileSync(filePath, content);
            console.log(colors.gray(`  ✓ Optimized ${file}`));
        }
    }
}

async function optimizeJS() {
    console.log(colors.yellow('⚡ Optimizing JavaScript files...'));

    const jsDir = path.join(DIST_DIR, 'js');
    if (!fs.existsSync(jsDir)) return;

    const jsFiles = fs.readdirSync(jsDir);

    for (const file of jsFiles) {
        if (file.endsWith('.js') && !file.includes('legacy')) {
            const filePath = path.join(jsDir, file);
            let content = fs.readFileSync(filePath, 'utf8');

            // Additional JS optimizations
            if (OPTIMIZATION_CONFIG.js.removeConsole) {
                content = content.replace(/console\.(log|info|debug|warn)\([^)]*\);?/g, '');
            }

            if (OPTIMIZATION_CONFIG.js.removeDebugger) {
                content = content.replace(/debugger;?/g, '');
            }

            fs.writeFileSync(filePath, content);
            console.log(colors.gray(`  ✓ Optimized ${file}`));
        }
    }
}

async function createCompressedAssets() {
    console.log(chalk.yellow('🗜️  Creating compressed assets...'));

    const assetDirs = ['css', 'js'];

    for (const dir of assetDirs) {
        const dirPath = path.join(DIST_DIR, dir);
        if (!await fs.pathExists(dirPath)) continue;

        const files = await fs.readdir(dirPath);

        for (const file of files) {
            const filePath = path.join(dirPath, file);
            const stats = await fs.stat(filePath);

            // Only compress files larger than threshold
            if (stats.size > OPTIMIZATION_CONFIG.compression.threshold) {
                const content = await fs.readFile(filePath);

                // Create gzip version
                if (OPTIMIZATION_CONFIG.compression.gzip) {
                    const gzipped = gzipSync(content, { level: 9 });
                    await fs.writeFile(filePath + '.gz', gzipped);
                }

                // Create brotli version
                if (OPTIMIZATION_CONFIG.compression.brotli) {
                    const brotliCompressed = brotliCompressSync(content, {
                        params: {
                            [require('zlib').constants.BROTLI_PARAM_QUALITY]: 11
                        }
                    });
                    await fs.writeFile(filePath + '.br', brotliCompressed);
                }

                console.log(chalk.gray(`  ✓ Compressed ${file} (${formatBytes(stats.size)} → ${formatBytes(gzipSync(content).length)} gzip)`));
            }
        }
    }
}

async function generateAssetManifest() {
    console.log(chalk.yellow('📋 Generating asset manifest...'));

    const manifest = {};
    const assetDirs = ['css', 'js'];

    for (const dir of assetDirs) {
        const dirPath = path.join(DIST_DIR, dir);
        if (!await fs.pathExists(dirPath)) continue;

        const files = await fs.readdir(dirPath);

        for (const file of files) {
            if (file.endsWith('.gz') || file.endsWith('.br')) continue;

            const filePath = path.join(dirPath, file);
            const stats = await fs.stat(filePath);

            manifest[file] = {
                size: stats.size,
                path: `/${dir}/${file}`,
                mtime: stats.mtime.toISOString(),
                compression: {
                    gzip: await fs.pathExists(filePath + '.gz'),
                    brotli: await fs.pathExists(filePath + '.br')
                }
            };
        }
    }

    await fs.writeFile(
        path.join(DIST_DIR, 'asset-manifest.json'),
        JSON.stringify(manifest, null, 2)
    );

    console.log(chalk.gray(`  ✓ Created asset manifest with ${Object.keys(manifest).length} assets`));
}

async function createCacheHeaders() {
    console.log(chalk.yellow('🏠 Creating cache headers configuration...'));

    const htaccess = `
# Happy Place Theme - Production Cache Headers
# Generated: ${new Date().toISOString()}

<IfModule mod_expires.c>
    ExpiresActive On

    # CSS and JavaScript - 1 year (with cache busting)
    ExpiresByType text/css "access plus 1 year"
    ExpiresByType application/javascript "access plus 1 year"
    ExpiresByType text/javascript "access plus 1 year"

    # Images - 6 months
    ExpiresByType image/png "access plus 6 months"
    ExpiresByType image/jpg "access plus 6 months"
    ExpiresByType image/jpeg "access plus 6 months"
    ExpiresByType image/gif "access plus 6 months"
    ExpiresByType image/webp "access plus 6 months"
    ExpiresByType image/svg+xml "access plus 6 months"

    # Fonts - 1 year
    ExpiresByType font/woff2 "access plus 1 year"
    ExpiresByType font/woff "access plus 1 year"
    ExpiresByType application/font-woff2 "access plus 1 year"
</IfModule>

<IfModule mod_deflate.c>
    # Compress text files
    AddOutputFilterByType DEFLATE text/plain
    AddOutputFilterByType DEFLATE text/html
    AddOutputFilterByType DEFLATE text/xml
    AddOutputFilterByType DEFLATE text/css
    AddOutputFilterByType DEFLATE application/xml
    AddOutputFilterByType DEFLATE application/xhtml+xml
    AddOutputFilterByType DEFLATE application/rss+xml
    AddOutputFilterByType DEFLATE application/javascript
    AddOutputFilterByType DEFLATE application/x-javascript
</IfModule>

<IfModule mod_headers.c>
    # Security headers
    Header always set X-Content-Type-Options nosniff
    Header always set X-Frame-Options DENY
    Header always set X-XSS-Protection "1; mode=block"

    # Cache control for assets with hashes
    <FilesMatch "\\.(css|js)$">
        Header set Cache-Control "public, max-age=31536000, immutable"
    </FilesMatch>
</IfModule>
`;

    await fs.writeFile(path.join(DIST_DIR, '.htaccess'), htaccess.trim());
    console.log(chalk.gray('  ✓ Created .htaccess with cache headers'));
}

async function showOptimizationStats() {
    console.log(chalk.blue('\n📊 Optimization Statistics:'));

    const manifest = await fs.readFile(path.join(DIST_DIR, 'asset-manifest.json'), 'utf8');
    const assets = JSON.parse(manifest);

    let totalSize = 0;
    let totalCompressed = 0;
    let filesWithCompression = 0;

    for (const [filename, asset] of Object.entries(assets)) {
        totalSize += asset.size;

        if (asset.compression.gzip) {
            try {
                const gzipFile = path.join(DIST_DIR, asset.path.substring(1) + '.gz');
                const gzipStats = await fs.stat(gzipFile);
                totalCompressed += gzipStats.size;
                filesWithCompression++;
            } catch (e) {
                totalCompressed += asset.size;
            }
        } else {
            totalCompressed += asset.size;
        }
    }

    const compressionRatio = ((totalSize - totalCompressed) / totalSize * 100).toFixed(1);

    console.log(chalk.gray(`  Total assets: ${Object.keys(assets).length}`));
    console.log(chalk.gray(`  Original size: ${formatBytes(totalSize)}`));
    console.log(chalk.gray(`  Compressed size: ${formatBytes(totalCompressed)}`));
    console.log(chalk.green(`  Compression ratio: ${compressionRatio}%`));
    console.log(chalk.gray(`  Files with compression: ${filesWithCompression}`));
}

function removeDuplicateCSS(css) {
    // Simple duplicate property removal within the same rule
    return css.replace(/([^{]+){([^}]+)}/g, (match, selector, properties) => {
        const props = properties.split(';').filter(Boolean);
        const uniqueProps = {};

        // Keep only the last occurrence of each property
        props.forEach(prop => {
            const [key] = prop.split(':');
            if (key) {
                uniqueProps[key.trim()] = prop;
            }
        });

        return selector + '{' + Object.values(uniqueProps).join(';') + '}';
    });
}

function formatBytes(bytes) {
    if (bytes === 0) return '0 B';
    const k = 1024;
    const sizes = ['B', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(1)) + ' ' + sizes[i];
}

// Run optimization if called directly
if (require.main === module) {
    optimizeProduction();
}

module.exports = { optimizeProduction };