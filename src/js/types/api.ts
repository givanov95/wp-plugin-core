export type HttpMethod =
	| "GET"
	| "POST"
	| "PUT"
	| "PATCH"
	| "DELETE"
	| "HEAD"
	| "OPTIONS";

export interface WpRestEndpoint {
	route: string;
	nonce: string;
	method: HttpMethod;
}

export interface WpRestUrlData {
	rest_url: string;
	rest_endpoints: Record<string, WpRestEndpoint>;
}

export interface RestFetchOptions extends RequestInit {
	headers?: Record<string, string>;
}
