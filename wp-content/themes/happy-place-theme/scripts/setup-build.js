#!/usr/bin/env node

/**
 * Build Setup Script
 * Automates the initial setup of the build process
 */

const fs = require('fs-extra');
const path = require('path');
const chalk = require('chalk');

const THEME_DIR = process.cwd();
const DIST_DIR = path.join(THEME_DIR, 'dist');

console.log(chalk.blue.bold('🏠 Happy Place Theme - Build Setup'));
console.log(chalk.gray('Setting up optimized asset bundling...\n'));

async function setupBuild() {
    try {
        // 1. Check if Node.js and npm are available
        console.log(chalk.yellow('📋 Checking prerequisites...'));
        
        // 2. Install dependencies if package.json exists
        if (fs.existsSync(path.join(THEME_DIR, 'package.json'))) {
            console.log(chalk.green('✅ package.json found'));
            console.log(chalk.yellow('📦 Run "npm install" to install dependencies'));
        } else {
            console.log(chalk.red('❌ package.json not found'));
            return;
        }
        
        // 3. Check source files
        console.log(chalk.yellow('\n📁 Checking source files...'));
        const srcDir = path.join(THEME_DIR, 'src');
        const srcCssDir = path.join(srcDir, 'css');
        const srcJsDir = path.join(srcDir, 'js');
        
        if (fs.existsSync(srcCssDir) && fs.existsSync(srcJsDir)) {
            console.log(chalk.green('✅ Source directories found'));
            
            // List entry points
            const cssFiles = fs.readdirSync(srcCssDir).filter(f => f.endsWith('.css'));
            const jsFiles = fs.readdirSync(srcJsDir).filter(f => f.endsWith('.js'));
            
            console.log(chalk.blue(`   CSS entry points: ${cssFiles.join(', ')}`));
            console.log(chalk.blue(`   JS entry points: ${jsFiles.join(', ')}`));
        } else {
            console.log(chalk.red('❌ Source directories not found'));
            return;
        }
        
        // 4. Check existing assets
        console.log(chalk.yellow('\n🎨 Checking existing assets...'));
        const assetsDir = path.join(THEME_DIR, 'assets');
        
        if (fs.existsSync(assetsDir)) {
            const cssFramework = path.join(assetsDir, 'css', 'framework');
            const jsBase = path.join(assetsDir, 'js');
            
            if (fs.existsSync(cssFramework) && fs.existsSync(jsBase)) {
                console.log(chalk.green('✅ Existing asset structure found'));
                
                // Count files
                const cssFiles = await countFiles(path.join(assetsDir, 'css'), '.css');
                const jsFiles = await countFiles(jsBase, '.js');
                
                console.log(chalk.blue(`   Found ${cssFiles} CSS files`));
                console.log(chalk.blue(`   Found ${jsFiles} JS files`));
            }
        }
        
        // 5. Setup instructions
        console.log(chalk.yellow('\n🚀 Setup Instructions:'));
        console.log(chalk.white('1. Run: npm install'));
        console.log(chalk.white('2. Development: npm run dev'));
        console.log(chalk.white('3. Production build: npm run build'));
        console.log(chalk.white('4. Clean build: npm run clean && npm run build'));
        
        // 6. Show expected output
        console.log(chalk.yellow('\n📊 Expected Build Output:'));
        console.log(chalk.gray('dist/'));
        console.log(chalk.gray('├── css/'));
        console.log(chalk.gray('│   ├── critical.css (inlined)'));
        console.log(chalk.gray('│   ├── core.[hash].css (~40KB)'));
        console.log(chalk.gray('│   ├── sitewide.[hash].css (~60KB)'));
        console.log(chalk.gray('│   ├── listings.[hash].css (~60KB)'));
        console.log(chalk.gray('│   ├── dashboard.[hash].css (~80KB)'));
        console.log(chalk.gray('│   └── archive.[hash].css (~40KB)'));
        console.log(chalk.gray('├── js/'));
        console.log(chalk.gray('│   ├── core.[hash].js (~25KB)'));
        console.log(chalk.gray('│   ├── sitewide.[hash].js (~35KB)'));
        console.log(chalk.gray('│   ├── listings.[hash].js (~40KB)'));
        console.log(chalk.gray('│   ├── dashboard.[hash].js (~50KB)'));
        console.log(chalk.gray('│   └── archive.[hash].js (~30KB)'));
        console.log(chalk.gray('└── manifest.json'));
        
        console.log(chalk.green.bold('\n✨ Setup check complete!'));
        
    } catch (error) {
        console.error(chalk.red('❌ Setup failed:'), error.message);
    }
}

async function countFiles(dir, ext) {
    if (!fs.existsSync(dir)) return 0;
    
    let count = 0;
    
    async function walk(currentDir) {
        const items = await fs.readdir(currentDir);
        
        for (const item of items) {
            const fullPath = path.join(currentDir, item);
            const stat = await fs.stat(fullPath);
            
            if (stat.isDirectory()) {
                await walk(fullPath);
            } else if (path.extname(item) === ext) {
                count++;
            }
        }
    }
    
    await walk(dir);
    return count;
}

// Run setup
setupBuild();