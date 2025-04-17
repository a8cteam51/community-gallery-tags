/**
 * WordPress dependencies
 */
import { createRoot } from "@wordpress/element";

/**
 * Internal dependencies
 */
import MediaUploads from "./MediaUploads";

const addUploadFiles = () => {
	const containers = document.getElementsByClassName("community-gallery-tags-uploads");

	[...containers].forEach((container) => {
		const root = createRoot(container);

		const id = container.getAttribute("data-id");
		const text = container.dataset.buttontext;

		root.render(<MediaUploads id={id} buttonText={text} />);
	});
};

const init = () => {
	addUploadFiles();
};

window.addEventListener("load", (event) => {
	init();
});
