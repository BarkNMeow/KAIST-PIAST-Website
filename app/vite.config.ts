import { defineConfig } from 'vite'
import react from '@vitejs/plugin-react-swc'
import liveReload from 'vite-plugin-live-reload'

// https://vitejs.dev/config/
export default defineConfig({
    plugins: [react(), liveReload(['/../public/**/.php',])],
    root: 'src',
    build: {
        outDir: '../../public/dist',
        emptyOutDir: true,
        manifest: true,
        rollupOptions: {
            // Entry point
            input: 'src/test.tsx'
        }
    },
    server: {
        strictPort: true,
        // The port number should be same as the one in the helper.php
        port: 4000,
    }
})
