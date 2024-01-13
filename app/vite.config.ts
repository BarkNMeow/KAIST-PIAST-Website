import { defineConfig, splitVendorChunkPlugin } from 'vite'
import react from '@vitejs/plugin-react-swc'
import liveReload from 'vite-plugin-live-reload'
// import 'vite/modulepreload-polyfill'

// https://vitejs.dev/config/
export default defineConfig({
    plugins: [
        react(),
        liveReload(['../public/*.php',]),
        splitVendorChunkPlugin(),
    ],
    root: 'src',
    build: {
        outDir: '../../public/dist',
        emptyOutDir: true,
        manifest: true,
        rollupOptions: {
            input: './src/test.tsx',
        }
    },
    server: {
        strictPort: true,
        // The port number should be same as the one in the helper.php
        port: 5173,
    }
})
