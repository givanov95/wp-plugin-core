interface ImportMeta {
	readonly env: ImportMetaEnv;
	readonly hot?: {
		accept: (callback?: () => void) => void;
		dispose: (callback: (data: any) => void) => void;
	};
}

interface ImportMetaEnv {
	readonly VITE_API_URL: string;
	readonly VITE_APP_NAME: string;
	// add more env variables here
}
