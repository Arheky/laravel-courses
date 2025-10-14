import { defineConfig } from 'vite'
import laravel from 'laravel-vite-plugin'
import vue from '@vitejs/plugin-vue'

export default defineConfig(({ command, mode }) => {
  const isProduction = command === 'build' || mode === 'production'

  return {
    plugins: [
      laravel({
        input: ['resources/css/app.css', 'resources/js/app.js'],
        refresh: true,
        buildDirectory: 'build', // Render için build klasörünü sabitliyoruz
        manifest: true,           // manifest.json oluşturulacak
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

    // 📦 Build ayarları
    build: {
      target: ['es2022', 'chrome90'],
      outDir: 'public/build',
      manifest: true,
      emptyOutDir: true,
      rollupOptions: {
        output: {
          chunkFileNames: 'js/[name]-[hash].js',
          entryFileNames: 'js/[name]-[hash].js',
          assetFileNames: 'assets/[name]-[hash][extname]',
        },
      },
    },

    // Server ayarları (local + Render uyumlu)
    server: {
      host: '0.0.0.0',
      port: 5173,
      strictPort: true,
      https: false,
      origin: isProduction
        ? 'https://laravel-courses.onrender.com' // Render Production
        : 'http://localhost:5173',               // Local Geliştirme
      hmr: {
        host: isProduction
          ? 'laravel-courses.onrender.com'
          : 'localhost',
      },
      watch: {
        usePolling: true,
      },
    },

    // Base path — Render ve local için uyumlu
    base: isProduction ? '/build/' : '/',
  }
})
