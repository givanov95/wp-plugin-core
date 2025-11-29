import { defineConfig } from "vite";
import path from "path";

export default defineConfig({
	root: path.resolve(__dirname, "src"),
	publicDir: path.resolve(__dirname, "public"),
	build: {
		outDir: path.resolve(__dirname, "dist"),
		emptyOutDir: true,
		manifest: true,
		rollupOptions: {
			input: {
				main: path.resolve(__dirname, "src/assets/js/main.ts"),
			},
		},
	},
	server: {
		cors: true,
		strictPort: true,
		port: 5173,
		host: true,
		proxy: {
			"/wp-content": {
				target: "http://youworthitbox.localhost",
				changeOrigin: true,
				secure: false,
			},
		},
	},
	resolve: {
		alias: {
			"@": path.resolve(__dirname, "src"),
			"@css": path.resolve(__dirname, "src/assets/css"),
			"@js": path.resolve(__dirname, "src/assets/js"),
		},
	},
});
