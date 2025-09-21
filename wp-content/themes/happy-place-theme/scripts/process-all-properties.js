#!/usr/bin/env node

/**
 * Process All Properties - Real Estate Bulk Image Processor
 *
 * Handles the structure where each property has its own folder
 * C:\Users\pat\Desktop\Web Sized Listing Photos\123 Main St\*.jpg
 *
 * Usage:
 * node scripts/process-all-properties.js
 */

const fs = require('fs').promises;
const path = require('path');
const { execSync } = require('child_process');

class PropertyBatchProcessor {
    constructor() {
        this.baseInputDir = "C:\\Users\\pat\\Desktop\\Web Sized Listing Photos";
        this.baseOutputDir = "C:\\Users\\pat\\Desktop\\Optimized-Ready-for-Upload";
        this.stats = {
            propertiesFound: 0,
            propertiesProcessed: 0,
            totalImages: 0,
            totalSizeOriginal: 0,
            totalSizeOptimized: 0,
            errors: []
        };
    }

    async process() {
        console.log('🏠 Real Estate Batch Processor Starting...\n');

        try {
            // Find all property directories
            const properties = await this.findPropertyDirectories();

            if (properties.length === 0) {
                console.log('❌ No property directories found');
                return;
            }

            console.log(`📁 Found ${properties.length} property directories\n`);
            this.stats.propertiesFound = properties.length;

            // Process each property
            for (let i = 0; i < properties.length; i++) {
                const property = properties[i];
                console.log(`\n🏠 Processing property ${i + 1}/${properties.length}: ${property.name}`);

                await this.processProperty(property, i + 1);
            }

            // Generate summary
            await this.generateSummary();

        } catch (error) {
            console.error('❌ Batch processing failed:', error.message);
            process.exit(1);
        }
    }

    async findPropertyDirectories() {
        const properties = [];

        try {
            const entries = await fs.readdir(this.baseInputDir, { withFileTypes: true });

            for (const entry of entries) {
                if (entry.isDirectory()) {
                    const propertyPath = path.join(this.baseInputDir, entry.name);

                    // Check if directory contains images
                    const imageCount = await this.countImagesInDirectory(propertyPath);

                    if (imageCount > 0) {
                        properties.push({
                            name: entry.name,
                            path: propertyPath,
                            imageCount: imageCount
                        });
                    } else {
                        console.log(`   ⚠️  Skipping ${entry.name} (no images found)`);
                    }
                }
            }
        } catch (error) {
            throw new Error(`Cannot read properties directory: ${error.message}`);
        }

        return properties.sort((a, b) => a.name.localeCompare(b.name));
    }

    async countImagesInDirectory(dirPath) {
        const supportedExtensions = ['.jpg', '.jpeg', '.png', '.webp', '.tiff', '.bmp'];
        let count = 0;

        try {
            const files = await fs.readdir(dirPath);

            for (const file of files) {
                const ext = path.extname(file).toLowerCase();
                if (supportedExtensions.includes(ext)) {
                    count++;
                }
            }
        } catch (error) {
            // Directory might not be accessible, skip it
            return 0;
        }

        return count;
    }

    async processProperty(property, index) {
        try {
            // Clean property name for output directory
            const cleanName = this.cleanPropertyName(property.name);
            const outputDir = path.join(this.baseOutputDir, cleanName);

            console.log(`   📸 ${property.imageCount} images to process`);
            console.log(`   📂 Input: ${property.path}`);
            console.log(`   📂 Output: ${outputDir}`);

            // Build command for individual property
            const command = `node scripts/local-image-processor.js --input "${property.path}" --output "${outputDir}" --property "${property.name}"`;

            console.log(`   🔄 Processing...`);

            // Execute the image processor
            const output = execSync(command, {
                cwd: path.dirname(__dirname),
                encoding: 'utf8',
                stdio: 'pipe'
            });

            // Parse output for statistics (basic parsing)
            const lines = output.split('\n');
            const processedLine = lines.find(line => line.includes('Images processed:'));
            const sizeLine = lines.find(line => line.includes('Space saved:'));

            if (processedLine) {
                const processed = parseInt(processedLine.match(/\d+/)[0]);
                this.stats.totalImages += processed;
            }

            this.stats.propertiesProcessed++;
            console.log(`   ✅ Property completed`);

        } catch (error) {
            console.error(`   ❌ Error processing ${property.name}:`, error.message);
            this.stats.errors.push({
                property: property.name,
                error: error.message
            });
        }
    }

    cleanPropertyName(name) {
        return name
            .replace(/[\\/:*?"<>|]/g, '-')  // Replace invalid characters
            .replace(/\s+/g, '-')          // Replace spaces with dashes
            .replace(/-+/g, '-')           // Collapse multiple dashes
            .toLowerCase()
            .trim('-');
    }

    async generateSummary() {
        console.log('\n========================================');
        console.log('📊 BATCH PROCESSING SUMMARY');
        console.log('========================================');
        console.log(`🏠 Properties found: ${this.stats.propertiesFound}`);
        console.log(`✅ Properties processed: ${this.stats.propertiesProcessed}`);
        console.log(`📸 Total images processed: ${this.stats.totalImages}`);

        if (this.stats.errors.length > 0) {
            console.log(`❌ Properties with errors: ${this.stats.errors.length}`);
            console.log('\nErrors:');
            this.stats.errors.forEach(error => {
                console.log(`   • ${error.property}: ${error.error}`);
            });
        }

        console.log('\n📂 Output Structure:');
        console.log(`   ${this.baseOutputDir}`);
        console.log(`   ├── property-1/`);
        console.log(`   │   ├── hero/`);
        console.log(`   │   ├── gallery/`);
        console.log(`   │   ├── cards/`);
        console.log(`   │   └── thumbnails/`);
        console.log(`   ├── property-2/`);
        console.log(`   └── ...`);

        console.log('\n🚀 Next Steps:');
        console.log('1. Review individual property reports in each output folder');
        console.log('2. Check for duplicates in duplicates/ folders');
        console.log('3. Upload optimized images to WordPress');
        console.log('4. Update property listings with new media');

        // Open output directory
        console.log('\n💾 Opening output directory...');
        try {
            execSync(`explorer "${this.baseOutputDir}"`, { stdio: 'ignore' });
        } catch (error) {
            console.log(`   📂 Manually open: ${this.baseOutputDir}`);
        }
    }
}

// CLI execution
async function main() {
    const processor = new PropertyBatchProcessor();
    await processor.process();
}

// Check if we have the required processor
try {
    const processorPath = path.join(__dirname, 'local-image-processor.js');
    require('fs').accessSync(processorPath);
} catch (error) {
    console.error('❌ local-image-processor.js not found');
    console.log('Make sure you run this from the theme directory');
    process.exit(1);
}

// Run if called directly
if (require.main === module) {
    main().catch(error => {
        console.error('❌ Fatal error:', error.message);
        process.exit(1);
    });
}

module.exports = PropertyBatchProcessor;