#!/usr/bin/env node

/**
 * Hero-Only Real Estate Image Processor
 *
 * Streamlined version that creates only high-quality hero images
 * - 2500px long side (maintains aspect ratio)
 * - Higher quality settings (90% JPEG, 88% WebP)
 * - Faster processing
 * - Duplicate detection still included
 *
 * Usage:
 * node scripts/hero-only-processor.js --input "./property-folder" --output "./optimized" --property "123 Main St"
 */

const fs = require('fs').promises;
const path = require('path');
const sharp = require('sharp');
const crypto = require('crypto');
const { createHash } = require('crypto');

class HeroOnlyProcessor {
    constructor(options = {}) {
        this.options = {
            inputDir: options.input || './input',
            outputDir: options.output || './optimized',
            propertyName: options.property || 'property',

            // High-quality hero profile
            heroProfile: {
                maxDimension: 2500,  // Long side max
                quality: 90,         // High quality JPEG
                webpQuality: 88,     // High quality WebP
                progressive: true,
                optimizeCoding: true
            },

            // Duplicate detection
            duplicateThreshold: 0.95,
            generateFormats: ['jpeg', 'webp'],

            // Processing options
            stripMetadata: true,
            maintainAspectRatio: true
        };

        this.processedImages = [];
        this.duplicates = [];
        this.imageHashes = new Map();
        this.stats = {
            processed: 0,
            duplicatesFound: 0,
            totalSizeOriginal: 0,
            totalSizeOptimized: 0,
            averageQuality: 0
        };
    }

    async process() {
        console.log('🏠 Hero-Only Image Processor Starting...\n');
        console.log(`📐 Target: ${this.options.heroProfile.maxDimension}px long side`);
        console.log(`🎨 Quality: ${this.options.heroProfile.quality}% JPEG, ${this.options.heroProfile.webpQuality}% WebP\n`);

        try {
            await this.setupDirectories();
            const images = await this.findImages();

            console.log(`📸 Found ${images.length} images to process`);

            if (images.length === 0) {
                console.log('❌ No images found in input directory');
                return;
            }

            for (let i = 0; i < images.length; i++) {
                const imagePath = images[i];
                console.log(`\n🔄 Processing ${i + 1}/${images.length}: ${path.basename(imagePath)}`);

                await this.processImage(imagePath, i + 1);
            }

            await this.generateReport();
            console.log('\n✅ Hero processing complete!');

        } catch (error) {
            console.error('❌ Processing failed:', error.message);
            process.exit(1);
        }
    }

    async setupDirectories() {
        const dirs = [
            this.options.outputDir,
            path.join(this.options.outputDir, 'hero'),
            path.join(this.options.outputDir, 'duplicates')
        ];

        for (const dir of dirs) {
            await fs.mkdir(dir, { recursive: true });
        }
    }

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

        return images.sort();
    }

    async processImage(imagePath, index) {
        try {
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

            // Get image metadata
            const metadata = await this.getImageMetadata(imagePath);

            // Generate hero images only
            await this.generateHeroImages(imagePath, metadata, index);

            this.stats.processed++;

        } catch (error) {
            console.error(`   ❌ Error processing ${path.basename(imagePath)}:`, error.message);
        }
    }

    async generatePerceptualHash(imagePath) {
        try {
            const buffer = await sharp(imagePath)
                .resize(32, 32, { fit: 'fill' })
                .grayscale()
                .raw()
                .toBuffer();

            return createHash('md5').update(buffer).digest('hex');
        } catch (error) {
            return createHash('md5').update(imagePath).digest('hex');
        }
    }

    checkForDuplicate(hash, imagePath) {
        for (const [existingHash, existingImage] of this.imageHashes) {
            if (existingHash === hash) {
                return existingImage;
            }
        }
        return null;
    }

    async handleDuplicate(imagePath, duplicate, index) {
        console.log(`   🔍 Duplicate detected: ${path.basename(imagePath)} matches ${path.basename(duplicate.path)}`);

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

    async getImageMetadata(imagePath) {
        try {
            const image = sharp(imagePath);
            const metadata = await image.metadata();

            return {
                width: metadata.width,
                height: metadata.height,
                format: metadata.format,
                hasAlpha: metadata.hasAlpha,
                orientation: metadata.orientation,
                aspectRatio: metadata.width / metadata.height
            };
        } catch (error) {
            console.error(`   ⚠️  Could not analyze ${path.basename(imagePath)}`);
            return { width: 0, height: 0, aspectRatio: 1 };
        }
    }

    async generateHeroImages(imagePath, metadata, index) {
        const baseName = this.generateSEOName(imagePath, index);
        const profile = this.options.heroProfile;

        console.log(`   📐 Original: ${metadata.width}x${metadata.height} (${metadata.format})`);

        try {
            const image = sharp(imagePath);

            // Calculate resize dimensions (maintain aspect ratio)
            const { width, height } = this.calculateHeroSize(metadata, profile.maxDimension);

            console.log(`   📐 Target: ${width}x${height}`);

            // Generate both JPEG and WebP versions
            for (const format of this.options.generateFormats) {
                await this.generateHeroFormat(image, format, baseName, width, height, profile);
            }

        } catch (error) {
            console.error(`   ❌ Error generating hero images:`, error.message);
        }
    }

    calculateHeroSize(metadata, maxDimension) {
        const { width, height } = metadata;

        // If image is already smaller than max, keep original size
        if (width <= maxDimension && height <= maxDimension) {
            return { width, height };
        }

        // Calculate new dimensions maintaining aspect ratio
        if (width > height) {
            // Landscape: limit width
            const newWidth = maxDimension;
            const newHeight = Math.round((height * maxDimension) / width);
            return { width: newWidth, height: newHeight };
        } else {
            // Portrait: limit height
            const newHeight = maxDimension;
            const newWidth = Math.round((width * maxDimension) / height);
            return { width: newWidth, height: newHeight };
        }
    }

    async generateHeroFormat(image, format, baseName, width, height, profile) {
        try {
            const outputPath = path.join(
                this.options.outputDir,
                'hero',
                `${baseName}.${format}`
            );

            let processor = image
                .clone()
                .resize(width, height, {
                    fit: 'inside',           // Maintain aspect ratio, no cropping
                    withoutEnlargement: true, // Don't make small images larger
                    background: { r: 255, g: 255, b: 255, alpha: 0 } // Transparent background if needed
                });

            // Apply format-specific optimizations
            if (format === 'jpeg') {
                processor = processor.jpeg({
                    quality: profile.quality,
                    progressive: profile.progressive,
                    optimizeCoding: profile.optimizeCoding,
                    mozjpeg: true  // Use mozjpeg for better compression
                });
            } else if (format === 'webp') {
                processor = processor.webp({
                    quality: profile.webpQuality,
                    effort: 6,
                    smartSubsample: true
                });
            }

            // Remove metadata if requested
            if (this.options.stripMetadata) {
                processor = processor.withMetadata({});
            }

            const info = await processor.toFile(outputPath);

            console.log(`   ✅ Hero ${format.toUpperCase()}: ${Math.round(info.size / 1024)}KB (${width}x${height})`);

            this.stats.totalSizeOptimized += info.size;

        } catch (error) {
            console.error(`   ❌ Error generating hero ${format}:`, error.message);
        }
    }

    generateSEOName(imagePath, index) {
        // Extract original filename (without extension)
        const originalName = path.basename(imagePath, path.extname(imagePath));

        // Extract property info from the property name (folder name)
        // Example: "115 Chestnut St Milton DE" -> include town and state
        const propertyParts = this.options.propertyName.split(' ');

        // Find town and state (typically last 2 parts)
        let town = '';
        let state = '';

        if (propertyParts.length >= 2) {
            state = propertyParts[propertyParts.length - 1]; // Last part (DE, MD, etc.)
            town = propertyParts[propertyParts.length - 2];  // Second to last (Milton, Dover, etc.)
        }

        // Clean up for SEO while maintaining your naming convention
        // Convert "115 Chestnut St 1" + "Milton DE" to "115-chestnut-st-1-milton-de"
        let seoName = originalName
            .toLowerCase()
            .replace(/\s+/g, '-')      // Replace spaces with dashes
            .replace(/[^a-z0-9-]/g, '') // Remove special characters but keep dashes
            .replace(/-+/g, '-')       // Collapse multiple dashes
            .trim('-');

        // Add town and state if available
        if (town && state) {
            const townState = `${town}-${state}`.toLowerCase();
            seoName = `${seoName}-${townState}`;
        }

        return seoName;
    }

    async generateReport() {
        const compressionRatio = Math.round((1 - this.stats.totalSizeOptimized / this.stats.totalSizeOriginal) * 100);

        const report = {
            timestamp: new Date().toISOString(),
            property: this.options.propertyName,
            processing: 'Hero images only',
            summary: {
                totalImages: this.stats.processed + this.stats.duplicatesFound,
                processedImages: this.stats.processed,
                duplicatesFound: this.stats.duplicatesFound,
                originalSize: this.formatBytes(this.stats.totalSizeOriginal),
                optimizedSize: this.formatBytes(this.stats.totalSizeOptimized),
                compressionRatio: compressionRatio,
                settings: {
                    maxDimension: this.options.heroProfile.maxDimension,
                    jpegQuality: this.options.heroProfile.quality,
                    webpQuality: this.options.heroProfile.webpQuality,
                    formats: this.options.generateFormats
                }
            },
            duplicates: this.duplicates.map(dup => ({
                original: path.basename(dup.original),
                duplicate: path.basename(dup.duplicate)
            }))
        };

        const reportPath = path.join(this.options.outputDir, 'hero-processing-report.json');
        await fs.writeFile(reportPath, JSON.stringify(report, null, 2));

        // Console summary
        console.log('\n📊 Hero Processing Summary:');
        console.log(`   🖼️  Images processed: ${report.summary.processedImages}`);
        console.log(`   🔍 Duplicates found: ${report.summary.duplicatesFound}`);
        console.log(`   📦 Original size: ${report.summary.originalSize}`);
        console.log(`   ✨ Optimized size: ${report.summary.optimizedSize}`);
        console.log(`   💾 Space saved: ${report.summary.compressionRatio}%`);
        console.log(`   📐 Max dimension: ${this.options.heroProfile.maxDimension}px`);
        console.log(`   🎨 Quality: JPEG ${this.options.heroProfile.quality}%, WebP ${this.options.heroProfile.webpQuality}%`);

        if (this.duplicates.length > 0) {
            console.log('\n🔍 Duplicates detected:');
            this.duplicates.forEach(dup => {
                console.log(`   • ${path.basename(dup.duplicate)} (duplicate of ${path.basename(dup.original)})`);
            });
        }

        // Generate upload instructions
        await this.generateUploadInstructions();
    }

    async generateUploadInstructions() {
        const instructions = `
# Hero Images Upload Instructions - ${this.options.propertyName}

## High-Quality Hero Images Ready

### Settings Used:
- **Max Dimension**: ${this.options.heroProfile.maxDimension}px (long side)
- **JPEG Quality**: ${this.options.heroProfile.quality}%
- **WebP Quality**: ${this.options.heroProfile.webpQuality}%
- **Aspect Ratio**: Maintained (no cropping)

### Output:
- **hero/** folder contains optimized images
- **duplicates/** folder contains any duplicates found

### WordPress Upload:
1. Upload images from hero/ folder to Media Library
2. Use as Featured Images for property listings
3. Use for hero sections and main gallery images

### File Naming:
Images are named: ${this.generateSEOName('example.jpg', 1)}.jpeg/.webp

### Performance:
- Original total: ${this.formatBytes(this.stats.totalSizeOriginal)}
- Optimized total: ${this.formatBytes(this.stats.totalSizeOptimized)}
- Space savings: ${Math.round((1 - this.stats.totalSizeOptimized / this.stats.totalSizeOriginal) * 100)}%

Perfect for high-quality property marketing while maintaining fast loading!
`;

        const instructionsPath = path.join(this.options.outputDir, 'HERO-UPLOAD-INSTRUCTIONS.md');
        await fs.writeFile(instructionsPath, instructions);
    }

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

    for (let i = 0; i < args.length; i += 2) {
        const key = args[i].replace('--', '');
        const value = args[i + 1];
        options[key] = value;
    }

    if (!options.input) {
        console.error('❌ --input directory is required');
        console.log('\nUsage:');
        console.log('node hero-only-processor.js --input ./photos --output ./optimized --property "123 Main St"');
        process.exit(1);
    }

    const processor = new HeroOnlyProcessor(options);
    await processor.process();
}

if (require.main === module) {
    main().catch(error => {
        console.error('❌ Fatal error:', error.message);
        process.exit(1);
    });
}

module.exports = HeroOnlyProcessor;