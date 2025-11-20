import {defineConfig} from 'vitest/config';
import vue from '@vitejs/plugin-vue';
import {quasar, transformAssetUrls} from '@quasar/vite-plugin';
import jsconfigPaths from 'vite-jsconfig-paths';
import path from 'path';

// https://vitejs.dev/config/
export default defineConfig({
  test: {
    globals: true,
    environment: 'happy-dom', // 'jsdom',
    setupFiles: 'test/vitest/setup-file.js',
    include: [
      // Matches vitest tests in any subfolder of 'src' or into 'test/vitest/__tests__'
      // Matches all files with extension 'js', 'jsx', 'ts' and 'tsx'
      'src/**/*.vitest.{test,spec}.{js,mjs,cjs,ts,mts,cts,jsx,tsx}',
      'test/vitest/__tests__/**/*.{test,spec}.{js,mjs,cjs,ts,mts,cts,jsx,tsx}',
    ],
    ui: true, // Включение UI
    server: {
      host: '0.0.0.0', // Чтобы UI был доступен извне контейнера
      port: 51204, // Порт, который вы указали в docker-compose
    },
  },
  plugins: [
    vue({
      template: {transformAssetUrls},
      devtools: true, // добавить!
    }),

    quasar({
      sassVariables: 'src/quasar-variables.scss',
    }),

    jsconfigPaths(),
  ],
  resolve: {
    alias: {
      '@': path.resolve(__dirname, 'src'), // Псевдоним @ указывает на /src
      '#q-app/wrappers': path.resolve(__dirname, 'node_modules/@quasar/app-vite/exports/wrappers/wrappers.js'),
    },
  },
});
