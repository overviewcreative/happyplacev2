import { defineConfig } from 'vite'
import legacy from '@vitejs/plugin-legacy'
import { resolve } from 'path'
import fs from 'fs'

/**
 * Vite Configuration for Happy Place Theme
 * Optimized for WordPress with conditional asset loading
 */
export default defineConfig(({ command, mode }) => {
  const isDev = command === 'serve'
  const isProd = mode === 'production'
  
  return {
    plugins: [
      legacy({
        targets: ['defaults', 'not IE 11']
      })
    ],
    
    // Development server configuration
    server: {
      host: 'localhost',
      port: 3000,
      cors: true,
      hmr: {
        host: 'localhost'
      }
    },
    
    // Build configuration
    build: {
      // Don't minify in development for easier debugging
      minify: isProd ? 'esbuild' : false,
      
      // Generate source maps for development
      sourcemap: isDev ? 'inline' : false,
      
      rollupOptions: {
        input: {
          // Critical CSS (will be inlined)
          'critical': resolve(__dirname, 'src/css/critical.css'),
          
          // Core bundles (always loaded)
          'core': resolve(__dirname, 'src/css/core.css'),
          'core-js': resolve(__dirname, 'src/js/core.js'),
          
          // Sitewide bundles (header/footer/nav)
          'sitewide': resolve(__dirname, 'src/css/sitewide.css'),
          'sitewide-js': resolve(__dirname, 'src/js/sitewide.js'),
          
          // Feature bundles (conditional loading)
          'listings': resolve(__dirname, 'src/css/listings.css'),
          'listings-js': resolve(__dirname, 'src/js/listings.js'),
          
          'dashboard': resolve(__dirname, 'src/css/dashboard.css'), 
          'dashboard-js': resolve(__dirname, 'src/js/dashboard.js'),
          
          'archive': resolve(__dirname, 'src/css/archive.css'),
          'archive-js': resolve(__dirname, 'src/js/archive.js'),
          
          'agents': resolve(__dirname, 'src/css/agents.css'),
          'agents-js': resolve(__dirname, 'src/js/agents.js'),
          
          'single-agent': resolve(__dirname, 'src/css/single-agent.css')
        },
        
        output: {
          dir: 'dist',
          // Clear naming for CSS
          assetFileNames: (assetInfo) => {
            const extType = assetInfo.name.split('.').pop()
            if (/css$/i.test(extType)) {
              return `css/[name]${isProd ? '.[hash]' : ''}.css`
            }
            return `assets/[name]${isProd ? '.[hash]' : ''}.[ext]`
          },
          // Clear naming for JS
          entryFileNames: (chunkInfo) => {
            return `js/[name]${isProd ? '.[hash]' : ''}.js`
          },
          chunkFileNames: (chunkInfo) => {
            return `js/chunks/[name]${isProd ? '.[hash]' : ''}.js`
          }
        },
        
        // External dependencies (don't bundle)
        external: ['jquery']
      },
      
      outDir: 'dist',
      assetsDir: 'assets',
      manifest: true, // Generate manifest for PHP integration
      emptyOutDir: true,
      
      // Optimize chunks
      chunkSizeWarningLimit: 500,
      
      // CSS code splitting
      cssCodeSplit: true
    },
    
    // CSS Processing
    css: {
      postcss: {
        plugins: [
          'autoprefixer',
          ...(isProd ? [
            ['cssnano', {
              preset: ['default', {
                discardComments: { removeAll: true },
                normalizeWhitespace: true,
                minifySelectors: true,
                minifyParams: true
              }]
            }]
          ] : [])
        ]
      }
    },
    
    // Resolve aliases for cleaner imports
    resolve: {
      alias: {
        '@': resolve(__dirname, 'src'),
        '@css': resolve(__dirname, 'assets/css'),
        '@js': resolve(__dirname, 'assets/js'),
        '@components': resolve(__dirname, 'template-parts'),
        '@framework': resolve(__dirname, 'assets/css/framework')
      }
    },
    
    // Define global constants
    define: {
      __DEV__: isDev,
      __PROD__: isProd
    },
    
    // Optimize dependencies
    optimizeDeps: {
      include: [
        // Add any npm packages that should be pre-bundled
      ],
      exclude: [
        'jquery' // WordPress provides this
      ]
    }
  }
})
