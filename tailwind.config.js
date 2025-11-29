/** @type {import('tailwindcss').Config} */
module.exports = {
	content: ["./**/*.php", "./src/**/*.{js,scss}"],
	theme: {
		extend: {
			colors: {
				yellow: "#fce473",
				pink: "#c71585",
				green: "#32cd32",
				blue: "#1d90ff",
				grey: "#efefef",
				primary: "#f6c2d9",
				secondary: "#ff78CB",
				third: "#ffd1f8",
				fourth: "#614542",
				text: "#595860",
				border: "#dcdcdc",
				"background-grey": "#f5f5f5",
			},
			// Fonts from your SCSS variables
			fontFamily: {
				sans: ["Montserrat", "system-ui", "sans-serif"],
			},
		},
	},
};
