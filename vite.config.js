import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import path from 'path';
import { fileURLToPath } from 'url';
import iconsPlugin from './vite.icons.plugin.js';

const __dirname = path.dirname(fileURLToPath(import.meta.url));

const inputs = [
  'resources/css/app.css',
  'resources/js/app.js',
  'resources/assets/css/demo.css',
  'resources/assets/vendor/fonts/iconify/iconify.css',
  'resources/assets/vendor/scss/core.scss',
  'resources/assets/vendor/scss/pages/page-auth.scss',
  'resources/assets/vendor/scss/pages/front-page.scss',
  'resources/assets/vendor/libs/node-waves/node-waves.scss',
  'resources/assets/vendor/libs/pickr/pickr-themes.scss',
  'resources/assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.scss',
  'resources/assets/vendor/libs/typeahead-js/typeahead.scss',
  'resources/assets/vendor/libs/quill/editor.scss',
  'resources/assets/vendor/libs/quill/typography.scss',
  'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss',
  'resources/assets/vendor/libs/jquery/jquery.js',
  'resources/assets/vendor/libs/popper/popper.js',
  'resources/assets/vendor/libs/node-waves/node-waves.js',
  'resources/assets/vendor/libs/pickr/pickr.js',
  'resources/assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.js',
  'resources/assets/vendor/libs/hammer/hammer.js',
  'resources/assets/vendor/libs/quill/quill.js',
  'resources/assets/vendor/libs/sweetalert2/sweetalert2.js',
  'resources/assets/vendor/js/helpers.js',
  'resources/assets/vendor/js/template-customizer.js',
  'resources/assets/vendor/js/bootstrap.js',
  'resources/assets/vendor/js/menu.js',
  'resources/assets/vendor/js/dropdown-hover.js',
  'resources/assets/vendor/js/mega-dropdown.js',
  'resources/assets/js/config.js',
  'resources/assets/js/front-config.js',
  'resources/assets/js/main.js',
  'resources/assets/js/front-main.js'
];

export default defineConfig({
  plugins: [
    laravel({ input: inputs, refresh: true }),
    iconsPlugin()
  ],
  resolve: {
    alias: {
      '@': path.resolve(__dirname, 'resources')
    }
  },
  css: {
    preprocessorOptions: {
      scss: {
        silenceDeprecations: ['import', 'global-builtin', 'color-functions', 'if-function']
      }
    }
  },
  build: {
    commonjsOptions: {
      include: [/node_modules/]
    }
  }
});
