import { defineConfig } from 'vite'
import laravel from 'laravel-vite-plugin'
import vue from '@vitejs/plugin-vue'

export default defineConfig(({ command, mode }) => {
  const isProduction = mode === 'production'

  return {
    plugins: [
      laravel({
        input: ['resources/css/app.css', 'resources/js/app.js'],
        refresh: true,
        buildDirectory: 'build',
      }),
      vue({
        template: {
          transformAssetUrls: {
            base: null,
            includeAbsolute: false,
          },
        },
      }),
    ],

    // Build ayarları
    build: {
      manifest: true,
      outDir: 'public/build',
      emptyOutDir: true,
      target: ['es2022', 'chrome90'],
      rollupOptions: {
        output: {
          chunkFileNames: 'js/[name]-[hash].js',
          entryFileNames: 'js/[name]-[hash].js',
          assetFileNames: 'assets/[name]-[hash][extname]',
        },
      },
    },

    // Server ayarları
    server: {
      host: '0.0.0.0',
      port: 5173,
      strictPort: true,
      https: false,
      origin: isProduction
        ? 'https://laravel-courses.onrender.com' // ✅ Render
        : 'http://localhost:5173',               // 💻 Local
      hmr: {
        host: isProduction
          ? 'laravel-courses.onrender.com'
          : 'localhost',
      },
      watch: {
        usePolling: true,
      },
    },

    // Base path (otomatik algıla)
    base: isProduction ? '/' : '/',
  }
})
