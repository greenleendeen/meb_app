import { defineConfig } from "vite";
import symfonyPlugin from "vite-plugin-symfony";

/* if you're using React */
// import react from '@vitejs/plugin-react';

export default defineConfig({
    plugins: [
        /* react(), // if you're using React */
        symfonyPlugin(),
    ],
    
    build: {
        rollupOptions: {
            input: {
                app: "./assets/app.js"
            },
        }
    },
})


/*
import { defineConfig } from 'vite';
import symfonyPlugin from 'vite-plugin-symfony';

export default defineConfig({
  plugins: [symfonyPlugin()],
  build: {
    outDir: 'public/build',
    emptyOutDir: true,
    manifest: true,
  },
  server: {
    strictPort: true,
    port: 5173,
  },
});*/