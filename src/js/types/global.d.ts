import { WpRestUrlData } from "./api";

declare global {
	interface Window {
		WpRestUrlData?: WpRestUrlData;
	}
}

export {};
