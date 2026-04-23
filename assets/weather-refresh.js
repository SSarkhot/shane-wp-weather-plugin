console.log("weather-refresh.js loaded");

document.addEventListener("DOMContentLoaded", function () {
	if (typeof shaneWeather === "undefined") {
		console.log("shaneWeather is undefined");
		return;
	}

	const introCard = document.getElementById("shane-weather-intro");
	const mainCard = document.getElementById("shane-weather-wrapper");

	if (parseInt(shaneWeather.enableIntro, 10) === 1 && introCard) {
		introCard.classList.add("is-visible");

		setTimeout(() => {
			introCard.remove();

			if (mainCard) {
				mainCard.classList.add("is-visible");
			}
		}, 6000);
	} else {
		if (mainCard) {
			mainCard.classList.add("is-visible");
		}
	}

	function refreshWeather() {
		const weatherWrapper = document.getElementById("shane-weather-wrapper");

		if (!weatherWrapper) return;

		weatherWrapper.style.opacity = "0.5";

		fetch(shaneWeather.ajaxUrl + "?action=shane_weather_refresh")
			.then((response) => response.text())
			.then((html) => {
				weatherWrapper.innerHTML = html;
				weatherWrapper.style.opacity = "1";
			})
			.catch((error) => {
				console.error("Weather refresh failed:", error);
				weatherWrapper.style.opacity = "1";
			});
	}

	setInterval(refreshWeather, 60000);
});