#!/usr/bin/env node

/**
 * Local Real Estate Image Processor
 *
 * Hybrid approach: Process images locally, then upload optimized versions
 *
 * Features:
 * - Bulk compression with quality profiles
 * - Duplicate detection using perceptual hashing
 * - Multiple format generation (WebP, AVIF, JPEG)
 * - Responsive size generation
 * - SEO-friendly naming
 * - Upload preparation
 *
 * Usage:
 * node scripts/local-image-processor.js --input ./property-photos --output ./optimized --property "123 Main St"
 */

const fs = require('fs').promises;
const path = require('path');
const sharp = require('sharp');
const crypto = require('crypto');
const { createHash } = require('crypto');

class LocalImageProcessor {
    constructor(options = {}) {
        this.options = {
            inputDir: options.input || './input',
            outputDir: options.output || './optimized',
            propertyName: options.property || 'property',

            // Compression profiles for real estate
            profiles: {
                hero: {
                    width: 1920,
                    height: 1080,
                    quality: 85,
                    webpQuality: 82,
                    avifQuality: 78
                },
                gallery: {
                    width: 1200,
                    height: 800,
                    quality: 82,
                    webpQuality: 80,
                    avifQuality: 75
                },
                thumbnail: {
                    width: 400,
                    height: 300,
                    quality: 80,
                    webpQuality: 78,
                    avifQuality: 72
                },
                card: {
                    width: 600,
                    height: 400,
                    quality: 78,
                    webpQuality: 75,
                    avifQuality: 70
                }
            },

            // Duplicate detection settings
            duplicateThreshold: 0.95,
            generateFormats: ['jpeg', 'webp'], // Add 'avif' if you have support

            // Processing options
            progressive: true,
            stripMetadata: true,
            optimizeForWeb: true
        };

        this.processedImages = [];
        this.duplicates = [];
        this.imageHashes = new Map();
        this.stats = {
            processed: 0,
            duplicatesFound: 0,
            totalSizeOriginal: 0,
            totalSizeOptimized: 0,
            formats: { jpeg: 0, webp: 0, avif: 0 }
        };
    }

    /**
     * Main processing workflow
     */
    async process() {
        console.log('🏠 Real Estate Image Processor Starting...\n');

        try {
            // Setup directories
            await this.setupDirectories();

            // Find and categorize images
            const images = await this.findImages();
            console.log(`📸 Found ${images.length} images to process`);

            if (images.length === 0) {
                console.log('❌ No images found in input directory');
                return;
            }

            // Process each image
            for (let i = 0; i < images.length; i++) {
                const imagePath = images[i];
                console.log(`\n🔄 Processing ${i + 1}/${images.length}: ${path.basename(imagePath)}`);

                await this.processImage(imagePath, i + 1);
            }

            // Generate summary report
            await this.generateReport();
            await this.generateUploadInstructions();

            console.log('\n✅ Processing complete!');

        } catch (error) {
            console.error('❌ Processing failed:', error.message);
            process.exit(1);
        }
    }

    /**
     * Setup output directories
     */
    async setupDirectories() {
        const dirs = [
            this.options.outputDir,
            path.join(this.options.outputDir, 'hero'),
            path.join(this.options.outputDir, 'gallery'),
            path.join(this.options.outputDir, 'thumbnails'),
            path.join(this.options.outputDir, 'cards'),
            path.join(this.options.outputDir, 'originals'),
            path.join(this.options.outputDir, 'duplicates')
        ];

        for (const dir of dirs) {
            await fs.mkdir(dir, { recursive: true });
        }
    }

    /**
     * Find all images in input directory
     */
    async findImages() {
        const supportedExtensions = ['.jpg', '.jpeg', '.png', '.webp', '.tiff', '.bmp'];
        const images = [];

        try {
            const files = await fs.readdir(this.options.inputDir);

            for (const file of files) {
                const ext = path.extname(file).toLowerCase();
                if (supportedExtensions.includes(ext)) {
                    images.push(path.join(this.options.inputDir, file));
                }
            }
        } catch (error) {
            throw new Error(`Cannot read input directory: ${error.message}`);
        }

        return images.sort(); // Consistent ordering
    }

    /**
     * Process individual image
     */
    async processImage(imagePath, index) {
        try {
            // Get original file stats
            const originalStats = await fs.stat(imagePath);
            this.stats.totalSizeOriginal += originalStats.size;

            // Generate perceptual hash for duplicate detection
            const hash = await this.generatePerceptualHash(imagePath);

            // Check for duplicates
            const duplicate = this.checkForDuplicate(hash, imagePath);
            if (duplicate) {
                await this.handleDuplicate(imagePath, duplicate, index);
                return;
            }

            this.imageHashes.set(hash, { path: imagePath, index });

            // Analyze image content for smart processing
            const analysis = await this.analyzeImageContent(imagePath);

            // Generate optimized versions
            await this.generateOptimizedVersions(imagePath, analysis, index);

            this.stats.processed++;

        } catch (error) {
            console.error(`   ❌ Error processing ${path.basename(imagePath)}:`, error.message);
        }
    }

    /**
     * Generate perceptual hash for duplicate detection
     */
    async generatePerceptualHash(imagePath) {
        try {
            // Resize to small size and convert to grayscale for hashing
            const buffer = await sharp(imagePath)
                .resize(32, 32, { fit: 'fill' })
                .grayscale()
                .raw()
                .toBuffer();

            // Create hash from pixel data
            return createHash('md5').update(buffer).digest('hex');

        } catch (error) {
            console.error(`   ⚠️  Could not generate hash for ${path.basename(imagePath)}`);
            return createHash('md5').update(imagePath).digest('hex'); // Fallback to filename hash
        }
    }

    /**
     * Check for duplicate images
     */
    checkForDuplicate(hash, imagePath) {
        for (const [existingHash, existingImage] of this.imageHashes) {
            // For now, exact hash match (can be improved with fuzzy matching)
            if (existingHash === hash) {
                return existingImage;
            }
        }
        return null;
    }

    /**
     * Handle duplicate images
     */
    async handleDuplicate(imagePath, duplicate, index) {
        console.log(`   🔍 Duplicate detected: ${path.basename(imagePath)} matches ${path.basename(duplicate.path)}`);

        // Copy to duplicates folder for review
        const duplicateName = `${index.toString().padStart(3, '0')}_duplicate_of_${duplicate.index.toString().padStart(3, '0')}_${path.basename(imagePath)}`;
        const duplicatePath = path.join(this.options.outputDir, 'duplicates', duplicateName);

        await fs.copyFile(imagePath, duplicatePath);

        this.duplicates.push({
            original: duplicate.path,
            duplicate: imagePath,
            index: index,
            originalIndex: duplicate.index
        });

        this.stats.duplicatesFound++;
    }

    /**
     * Analyze image content for smart processing
     */
    async analyzeImageContent(imagePath) {
        try {
            const image = sharp(imagePath);
            const metadata = await image.metadata();

            // Basic content analysis
            const analysis = {
                width: metadata.width,
                height: metadata.height,
                format: metadata.format,
                hasAlpha: metadata.hasAlpha,
                orientation: metadata.orientation,

                // Determine image category based on dimensions and content
                category: this.categorizeImage(metadata, path.basename(imagePath)),

                // Suggest processing profile
                profile: 'gallery' // Default
            };

            // Adjust profile based on category
            if (analysis.category === 'hero' || analysis.category === 'exterior') {
                analysis.profile = 'hero';
            } else if (analysis.category === 'thumbnail' || analysis.width < 600) {
                analysis.profile = 'thumbnail';
            }

            return analysis;

        } catch (error) {
            console.error(`   ⚠️  Could not analyze ${path.basename(imagePath)}`);
            return { category: 'unknown', profile: 'gallery' };
        }
    }

    /**
     * Categorize image based on filename and metadata
     */
    categorizeImage(metadata, filename) {
        const name = filename.toLowerCase();

        // Check filename patterns for real estate
        if (name.includes('hero') || name.includes('main') || name.includes('front')) return 'hero';
        if (name.includes('exterior') || name.includes('outside') || name.includes('facade')) return 'exterior';
        if (name.includes('kitchen')) return 'kitchen';
        if (name.includes('bathroom') || name.includes('bath')) return 'bathroom';
        if (name.includes('bedroom') || name.includes('bed')) return 'bedroom';
        if (name.includes('living') || name.includes('family')) return 'living';
        if (name.includes('garage')) return 'garage';
        if (name.includes('yard') || name.includes('garden') || name.includes('pool')) return 'outdoor';
        if (name.includes('thumb') || metadata.width < 500) return 'thumbnail';

        // Default categorization based on dimensions
        if (metadata.width >= 1600) return 'hero';
        if (metadata.width < 600) return 'thumbnail';

        return 'interior';
    }

    /**
     * Generate optimized versions of image
     */
    async generateOptimizedVersions(imagePath, analysis, index) {
        const profile = this.options.profiles[analysis.profile];
        const baseName = this.generateSEOName(imagePath, analysis, index);

        console.log(`   📐 Profile: ${analysis.profile} | Category: ${analysis.category}`);

        try {
            const image = sharp(imagePath);

            // Generate responsive sizes
            const sizes = [
                { name: 'hero', profile: this.options.profiles.hero, dir: 'hero' },
                { name: 'gallery', profile: this.options.profiles.gallery, dir: 'gallery' },
                { name: 'card', profile: this.options.profiles.card, dir: 'cards' },
                { name: 'thumbnail', profile: this.options.profiles.thumbnail, dir: 'thumbnails' }
            ];

            for (const size of sizes) {
                for (const format of this.options.generateFormats) {
                    await this.generateOptimizedSize(image, size, format, baseName, index);
                }
            }

            // Copy original for backup (optional)
            if (this.options.keepOriginals) {
                const originalPath = path.join(this.options.outputDir, 'originals', `${baseName}.original${path.extname(imagePath)}`);
                await fs.copyFile(imagePath, originalPath);
            }

        } catch (error) {
            console.error(`   ❌ Error generating versions:`, error.message);
        }
    }

    /**
     * Generate specific size and format
     */
    async generateOptimizedSize(image, size, format, baseName, index) {
        try {
            const outputPath = path.join(
                this.options.outputDir,
                size.dir,
                `${baseName}.${format}`
            );

            let processor = image
                .clone()
                .resize(size.profile.width, size.profile.height, {
                    fit: 'cover',
                    position: 'center'
                });

            // Apply format-specific optimizations
            if (format === 'jpeg') {
                processor = processor.jpeg({
                    quality: size.profile.quality,
                    progressive: this.options.progressive,
                    optimizeCoding: true
                });
            } else if (format === 'webp') {
                processor = processor.webp({
                    quality: size.profile.webpQuality,
                    effort: 6
                });
            } else if (format === 'avif') {
                processor = processor.avif({
                    quality: size.profile.avifQuality,
                    effort: 9
                });
            }

            // Remove metadata if requested
            if (this.options.stripMetadata) {
                processor = processor.withMetadata({ exif: {} });
            }

            const info = await processor.toFile(outputPath);

            console.log(`   ✅ ${size.name} ${format}: ${Math.round(info.size / 1024)}KB`);

            this.stats.totalSizeOptimized += info.size;
            this.stats.formats[format]++;

        } catch (error) {
            console.error(`   ❌ Error generating ${size.name} ${format}:`, error.message);
        }
    }

    /**
     * Generate SEO-friendly filename
     */
    generateSEOName(imagePath, analysis, index) {
        const propertySlug = this.options.propertyName
            .toLowerCase()
            .replace(/[^a-z0-9\s-]/g, '')
            .replace(/\s+/g, '-')
            .replace(/-+/g, '-')
            .trim('-');

        const category = analysis.category;
        const indexStr = index.toString().padStart(3, '0');

        return `${propertySlug}-${category}-${indexStr}`;
    }

    /**
     * Generate processing report
     */
    async generateReport() {
        const report = {
            timestamp: new Date().toISOString(),
            property: this.options.propertyName,
            summary: {
                totalImages: this.stats.processed + this.stats.duplicatesFound,
                processedImages: this.stats.processed,
                duplicatesFound: this.stats.duplicatesFound,
                originalSize: this.formatBytes(this.stats.totalSizeOriginal),
                optimizedSize: this.formatBytes(this.stats.totalSizeOptimized),
                compressionRatio: Math.round((1 - this.stats.totalSizeOptimized / this.stats.totalSizeOriginal) * 100),
                formatCounts: this.stats.formats
            },
            duplicates: this.duplicates.map(dup => ({
                original: path.basename(dup.original),
                duplicate: path.basename(dup.duplicate),
                originalIndex: dup.originalIndex,
                duplicateIndex: dup.index
            }))
        };

        const reportPath = path.join(this.options.outputDir, 'processing-report.json');
        await fs.writeFile(reportPath, JSON.stringify(report, null, 2));

        // Console summary
        console.log('\n📊 Processing Summary:');
        console.log(`   🖼️  Images processed: ${report.summary.processedImages}`);
        console.log(`   🔍 Duplicates found: ${report.summary.duplicatesFound}`);
        console.log(`   📦 Original size: ${report.summary.originalSize}`);
        console.log(`   ✨ Optimized size: ${report.summary.optimizedSize}`);
        console.log(`   💾 Space saved: ${report.summary.compressionRatio}%`);

        if (this.duplicates.length > 0) {
            console.log('\n🔍 Duplicates detected:');
            this.duplicates.forEach(dup => {
                console.log(`   • ${path.basename(dup.duplicate)} (duplicate of ${path.basename(dup.original)})`);
            });
        }
    }

    /**
     * Generate upload instructions
     */
    async generateUploadInstructions() {
        const instructions = `
# Upload Instructions for ${this.options.propertyName}

## Optimized Images Ready for Upload

### Directory Structure:
- **hero/**: Large format images for property hero sections
- **gallery/**: Standard gallery images for property listings
- **cards/**: Smaller images for property cards and previews
- **thumbnails/**: Thumbnail images for quick loading
- **duplicates/**: Detected duplicates (review before upload)

### Upload Process:
1. **WordPress Media Library**: Upload images from the appropriate folders
2. **ACF Gallery Fields**: Use gallery/ folder images
3. **Featured Images**: Use hero/ folder images
4. **Property Cards**: Use cards/ folder images

### File Naming Convention:
${this.generateSEOName('example.jpg', { category: 'kitchen' }, 1)}.webp
- Property name + room/area + sequence number
- SEO-friendly and organized

### Compression Results:
- Original total size: ${this.formatBytes(this.stats.totalSizeOriginal)}
- Optimized total size: ${this.formatBytes(this.stats.totalSizeOptimized)}
- Space savings: ${Math.round((1 - this.stats.totalSizeOptimized / this.stats.totalSizeOriginal) * 100)}%

### Next Steps:
1. Review duplicates in duplicates/ folder
2. Upload optimized images to WordPress
3. Update property listing with new media
4. Test loading performance
`;

        const instructionsPath = path.join(this.options.outputDir, 'UPLOAD-INSTRUCTIONS.md');
        await fs.writeFile(instructionsPath, instructions);

        console.log(`\n📋 Upload instructions saved to: ${instructionsPath}`);
    }

    /**
     * Format bytes for human readability
     */
    formatBytes(bytes) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    }
}

// CLI Interface
async function main() {
    const args = process.argv.slice(2);
    const options = {};

    // Parse command line arguments
    for (let i = 0; i < args.length; i += 2) {
        const key = args[i].replace('--', '');
        const value = args[i + 1];
        options[key] = value;
    }

    // Validate required options
    if (!options.input) {
        console.error('❌ --input directory is required');
        console.log('\nUsage:');
        console.log('node local-image-processor.js --input ./photos --output ./optimized --property "123 Main St"');
        process.exit(1);
    }

    const processor = new LocalImageProcessor(options);
    await processor.process();
}

// Check if we have sharp installed
try {
    require('sharp');
} catch (error) {
    console.error('❌ Sharp image processing library not found.');
    console.log('Install it with: npm install sharp');
    process.exit(1);
}

// Run if called directly
if (require.main === module) {
    main().catch(error => {
        console.error('❌ Fatal error:', error.message);
        process.exit(1);
    });
}

module.exports = LocalImageProcessor;