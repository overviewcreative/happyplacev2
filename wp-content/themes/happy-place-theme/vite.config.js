import { defineConfig } from 'vite'
import legacy from '@vitejs/plugin-legacy'
import { resolve } from 'path'

/**
 * Vite Configuration - Optimized for Production Performance
 * Updated: September 17, 2025 - Post-Emergency Launch Optimization
 */
export default defineConfig(({ command, mode }) => {
  const isDev = command === 'serve'
  const isProd = mode === 'production'

  return {
    plugins: [
      // ELIMINATED: Legacy plugin to reduce build duplicates
      // Modern browsers only - targets ES2015+ (Chrome 58+, Firefox 57+, Safari 11+)
    ],

    build: {
      // Enhanced minification settings for production
      minify: isProd ? 'esbuild' : false,
      sourcemap: isDev ? 'inline' : false,

      // Production optimization settings
      target: ['es2015', 'chrome58', 'firefox57', 'safari11'],
      cssTarget: 'chrome61',

      // OPTIMIZED: Target <500KB total bundles (86% reduction from 2.8MB)
      chunkSizeWarningLimit: 150, // Alert for bundles >150KB
      assetsInlineLimit: 8192, // Inline assets smaller than 8KB for better performance

      // Enhanced Rollup configuration for production optimization
      rollupOptions: {
        // Tree shaking and dead code elimination
        treeshake: {
          preset: 'recommended',
          unknownGlobalSideEffects: false,
          propertyReadSideEffects: false
        },

        input: {
          // FOUNDATION: Essential CSS foundation (reset, typography, layout, utilities)
          'core': resolve(__dirname, 'src/css/core.css'),

          // CRITICAL: Essential header/footer only (<15KB target)
          'critical-optimized': resolve(__dirname, 'src/css/critical-optimized.css'),

          // MODULAR BUNDLES: Page-specific optimized CSS
          'homepage': resolve(__dirname, 'src/css/homepage.css'),
          'listings-archive': resolve(__dirname, 'src/css/listings-archive.css'),
          'single-property': resolve(__dirname, 'src/css/single-property.css'),

          // CORE: Essential JavaScript only (no legacy)
          'core-js': resolve(__dirname, 'src/js/core.js'),
          'sitewide-js': resolve(__dirname, 'src/js/sitewide.js'),

          // PAGE BUNDLES: Optimized JavaScript
          'listings-js': resolve(__dirname, 'src/js/listings.js'),
          'archive-js': resolve(__dirname, 'src/js/archive.js'),
          'dashboard-js': resolve(__dirname, 'src/js/dashboard.js'),

          // SPECIALIZED: Minimal bundles
          'agents-js': resolve(__dirname, 'src/js/agents.js'),
        },

        output: {
          dir: 'dist',

          // Optimized file naming with cache busting
          assetFileNames: (assetInfo) => {
            if (assetInfo.name.endsWith('.css')) {
              return isProd ? `css/[name]-[hash].min.css` : `css/[name].css`
            }
            return isProd ? `assets/[name]-[hash].[ext]` : `assets/[name].[ext]`
          },

          entryFileNames: (chunkInfo) => {
            // Handle JS files with proper cache busting
            if (chunkInfo.name.endsWith('-js')) {
              return isProd ? `js/[name]-[hash].min.js` : `js/[name].js`
            }
            // Handle CSS wrapper files
            return isProd ? `js/[name]-[hash].min.js` : `js/[name].js`
          },

          // Improved chunk naming for better caching
          chunkFileNames: isProd ? `js/chunks/[name]-[hash].min.js` : `js/chunks/[name].js`,

          // OPTIMIZED: Minimal chunking for modular bundles
          manualChunks: (id) => {
            // Vendor chunk for external libraries only
            if (id.includes('node_modules')) {
              return 'vendor'
            }
            // Keep utilities separate for tree-shaking efficiency
            if (id.includes('assets/js/utilities/')) {
              return 'utilities'
            }
            // Core framework components (shared across pages)
            if (id.includes('assets/js/core/')) {
              return 'framework-core'
            }
          }
        },

        external: ['jquery']
      },

      // Output directory and manifest
      outDir: 'dist',
      emptyOutDir: true,
      manifest: true,
      cssCodeSplit: true,

      // Additional production optimizations
      reportCompressedSize: isProd,
      write: true,

      // CSS optimization
      cssMinify: isProd ? 'esbuild' : false,

      // Advanced compression settings
      terserOptions: isProd ? {
        compress: {
          drop_console: true,
          drop_debugger: true,
          pure_funcs: ['console.log', 'console.info', 'console.debug'],
          passes: 2
        },
        mangle: {
          safari10: true,
          properties: {
            regex: /^_/
          }
        },
        format: {
          comments: false,
          safari10: true
        }
      } : {}
    },

    // Development server configuration
    server: {
      host: true,
      port: 3000,
      strictPort: false,
      open: false
    },

    // CSS preprocessing optimization
    css: {
      devSourcemap: isDev,
      preprocessorOptions: {
        scss: {
          additionalData: `
            @import "@framework/core/variables.css";
            @import "@framework/core/tokens.css";
          `
        }
      },
      postcss: './postcss.config.js'
    },

    // Enhanced path aliases for better import resolution
    resolve: {
      alias: {
        '@': resolve(__dirname, 'src'),
        '@css': resolve(__dirname, 'src/css'),
        '@framework': resolve(__dirname, 'assets/css/framework'),
        '@js': resolve(__dirname, 'src/js'),
        '@assets': resolve(__dirname, 'assets'),
        '@components': resolve(__dirname, 'assets/js/components'),
        '@utilities': resolve(__dirname, 'assets/js/utilities'),
        '@features': resolve(__dirname, 'assets/js/features')
      }
    },

    // Performance optimizations
    optimizeDeps: {
      include: ['jquery'],
      exclude: []
    },

    // Preview configuration for production testing
    preview: {
      port: 4173,
      host: true,
      strictPort: false
    }
  }
})