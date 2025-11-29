import { SpeedyManager } from "../components/SpeedyManager";
import { SubscriptionManager } from "../components/SubscriptionManager";

export function initSubscriptions() {
	document.addEventListener("DOMContentLoaded", () => {
		const subscriptionForms = document.querySelectorAll<HTMLFormElement>(
			".js-subscription-form"
		);

		subscriptionForms.forEach((subscriptionForm) => {
			new SubscriptionManager(subscriptionForm);
			new SpeedyManager(subscriptionForm);
		});
	});
}
