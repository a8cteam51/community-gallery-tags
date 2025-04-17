/**
 * WordPress dependencies
 */
import { __, sprintf } from "@wordpress/i18n";
import { FormFileUpload, DropZone, Spinner } from "@wordpress/components";
import apiRequest from "@wordpress/api-request";
import { useState, useRef, useEffect } from "@wordpress/element";

const MediaUploads = ({ id, buttonText }) => {
	const [loading, setLoading] = useState(false);
	const [uploadFiles, setUploadFiles] = useState([]);
	const [uploadComplete, setUploadComplete] = useState();
	const [showHide, setShowHide] = useState(false);

	console.log("MediaUploads", id, buttonText);

	const ref = useRef(uploadFiles);

	useEffect(() => {
		if (uploadComplete) {
			document.dispatchEvent(uploadComplete);
		}
	}, [uploadComplete]);

	const getFilesFromButton = (event) => {
		const files = event.target.files;
		getFilesFromDropZone(files);
	};

	const getFilesFromDropZone = (files) => {
		if (files.length) {
			const uploads = [];

			[...files].forEach((file, index) => {
				const isLast = index === files.length - 1;

				setLoading(true);
				uploadFile(file, index, isLast);
				uploads.push({ name: file.name });
			});

			setUploadFiles([...uploads]);
			ref.current = [...uploads];
		}
	};

	const uploadFile = async (file, index, isLast) => {
		if (!file || !file.name) {
			return;
		}
		const form = new FormData();

		form.append("post", id);
		form.append("file", file);

		const headers = {
			"Content-Disposition": `attachment; filename=${file?.name}`,
		};

		apiRequest({
			path: "/wp/v2/media",
			method: "POST",
			headers: headers,
			data: form,
			processData: false,
			contentType: false,
		})
			.then((result) => {
				const newUploaded = [...ref.current];
				newUploaded[index] = result;

				setUploadFiles(newUploaded);
				ref.current = newUploaded;
			})
			.then(() => {
				if (isLast) {
					const imgUploaded = new CustomEvent("imagesUploaded", {
						detail: {
							images: ref.current,
						},
					});
					setLoading(false);
					setUploadComplete(imgUploaded);
					document.body.dispatchEvent(imgUploaded);
				}
			})
			.catch((error) => {
				console.log(error);
				const newError = [...ref.current];
				newError[index] = {
					error: {
						name: file?.name,
						message:
							error?.responseJSON?.message ??
							__("This file could not be uploaded", "community-gallery-tags"),
					},
				};

				setUploadFiles(newError);
				ref.current = newError;
			});
	};

	return (
		<>
			{showHide ? (
				<>
					<FormFileUpload
						onChange={getFilesFromButton}
						className="community-gallery-tags-uploads__button"
						multiple={true}
						accept="image/*,video/*"
						disabled={loading}
					>
						<DropZone onFilesDrop={ getFilesFromDropZone } label={ buttonText } />
						
					</FormFileUpload>

					{uploadFiles.length > 0 && (
						<ul className="community-gallery-tags-uploads__uploaded-files">
							{[...uploadFiles].map((file, index) => (
								<li key={index}>
									{file?.name && (
										<>
											{sprintf(
												/* translators: %s: file name */
												__("%s is loading", "community-gallery-tags"),
												file?.name,
											)}
											<Spinner />
										</>
									)}

									{file?.title && (
										<>
											<img
												width="50"
												src={file?.guid?.rendered}
												alt={file?.alt_text}
											/>
											{sprintf(
												/* translators: %s: file name */
												__("%s has been uploaded", "community-gallery-tags"),
												file?.title?.rendered,
											)}
										</>
									)}

									{file?.error && (
										<>
											{sprintf(
												"%s %s",
												file?.error?.name,
												file?.error?.message,
											)}
										</>
									)}
								</li>
							))}
						</ul>
					)}
				</>
			) : (
				<button
					onClick={() => {
						setShowHide(true);
					}}
					className="community-gallery-tags-uploads__show-hide wp-block-button__link wp-element-button"
				>
					{__("+ Add to gallery", "community-gallery-tags")}
				</button>
			)}
		</>
	);
};

export default MediaUploads;
