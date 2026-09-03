document.addEventListener('DOMContentLoaded', () => {
	const root = document;
	root.querySelectorAll('[data-licencepress-count]').forEach((element) => {
		element.classList.add('licencepress-count-ready');
	});
});
