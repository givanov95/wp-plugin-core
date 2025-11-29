import { HttpMethod, RestFetchOptions, WpRestEndpoint } from "../types/api";

export abstract class RestApi {
	protected getRestEndpoint(
		route: string,
		method: HttpMethod
	): WpRestEndpoint | null {
		if (!window.WpRestUrlData) return null;

		const normalizedRoute = route.startsWith("/") ? route.slice(1) : route;
		const endpointKey = `${normalizedRoute}|${method}`;

		return window.WpRestUrlData.rest_endpoints[endpointKey] || null;
	}

	protected async restFetch<T>(
		route: string,
		method: HttpMethod,
		data?: unknown,
		options: RestFetchOptions = {}
	): Promise<T> {
		const endpoint = this.getRestEndpoint(route, method);
		if (!endpoint) {
			throw new Error(
				`Missing REST endpoint for '${route}' with method '${method}'`
			);
		}

		const url = `${window.WpRestUrlData!.rest_url}${endpoint.route}`;

		const shouldIncludeBody =
			method !== "GET" && method !== "HEAD" && data !== undefined;

		const fetchOptions: RequestInit = {
			method: endpoint.method,
			headers: {
				"Content-Type": "application/json",
				"X-WP-Nonce": endpoint.nonce,
				...options.headers,
			},
			...options,
		};

		if (shouldIncludeBody) {
			fetchOptions.body = JSON.stringify(data);
		}

		const response = await fetch(url, fetchOptions);

		if (!response.ok) {
			let errorMessage = `HTTP error ${response.status}`;

			try {
				const errorData = (await response.json()) as {
					message?: string;
				};
				errorMessage = errorData.message
					? `HTTP error ${response.status}: ${errorData.message}`
					: `HTTP error ${response.status}`;
			} catch {
				errorMessage = `HTTP error ${response.status}: ${response.statusText}`;
			}

			throw new Error(errorMessage);
		}

		const responseData = await response.json();

		return (responseData.data ?? responseData) as T;
	}
}
