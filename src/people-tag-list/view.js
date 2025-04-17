let peopleContainer;

const isVisible = (parent, child) => {
	return (
		child.offsetLeft - parent.offsetLeft > parent.offsetWidth ||
		child.offsetTop - parent.offsetLeft > parent.offsetHeight
	);
};

const clipped = (people) => {
	const peopleContainer = people.querySelector(".people-tax-list__links");
	const peopleList = peopleContainer.childNodes
		? [...peopleContainer.childNodes]
		: null;
	const peopleShowBtn = people.querySelector(".people-tax-list__show-all");

	if (!peopleList) return;

	peopleList.forEach((item) => {
		const isHidden = isVisible(peopleContainer, item);

		if (isHidden) {
			item.ariaDisabled = true;
			item.tabIndex = "-1";
		} else {
			item.ariaDisabled = false;
			item.tabIndex = "0";
		}
	});

	peopleList.some((e) => e.ariaDisabled === "true")
		? peopleShowBtn.classList.remove("hidden")
		: peopleShowBtn.classList.add("hidden");
};

const events = (people) => {
	const peopleShowBtn = people.querySelector(".people-tax-list__show-all");

	if (!peopleShowBtn) return;

	peopleShowBtn.addEventListener("click", (event) => {
		people.classList.toggle("clip-taxonomy");
		peopleShowBtn.remove();

		const peopleContainer = people.querySelector(".people-tax-list__links");
		const peopleList = peopleContainer.childNodes;

		[...peopleList].forEach((item) => {
			item.ariaDisabled = false;
			item.tabIndex = "0";
		});
	});

	window.addEventListener("resize", () => {
		clipped(people);
	});
};

const init = () => {
	[...peopleContainer].forEach((people) => {
		people.classList.add("clip-taxonomy");

		events(people);
		clipped(people);
	});
};

window.addEventListener("load", (event) => {
	peopleContainer = document.querySelectorAll(
		".wp-block-community-gallery-tags-people-tag-list",
	);

	if (!peopleContainer) return;

	init();
});
