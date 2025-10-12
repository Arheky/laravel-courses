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

    // 📦 Build ayarları (Render için optimize)
    build: {
      target: ['es2022', 'chrome90'],
      outDir: 'public/build',
      manifest: true,
      rollupOptions: {
        output: {
          chunkFileNames: 'js/[name]-[hash].js',
          entryFileNames: 'js/[name]-[hash].js',
          assetFileNames: 'assets/[name]-[hash][extname]',
        },
      },
    },

    // 🧠 Server ayarları (Local + Render uyumlu)
    server: {
      host: '0.0.0.0',
      port: 5173,
      strictPort: true,
      https: false,
      hmr: {
        host: isProduction
          ? 'laravel-courses.onrender.com' // ✅ Render production
          : 'localhost',                   // 💻 Local geliştirme
      },
      watch: {
        usePolling: true,
      },
    },

    // 🌐 Mixed Content (HTTPS/HTTP) fix
    base: isProduction ? '/' : 'http://localhost:5173/',
  }
})
