(function (wp) {
	var el = wp.element.createElement;
	var registerBlockType = wp.blocks.registerBlockType;
	var InspectorControls = wp.blockEditor ? wp.blockEditor.InspectorControls : wp.editor.InspectorControls;
	var PanelBody = wp.components.PanelBody;
	var TextControl = wp.components.TextControl;
	var SelectControl = wp.components.SelectControl;
	var Button = wp.components.Button;
	var TextareaControl = wp.components.TextareaControl;
	var Placeholder = wp.components.Placeholder;
	var Spinner = wp.components.Spinner;
	var serverSideRender = wp.serverSideRender;
	var withSelect = wp.data.withSelect;
	var withDispatch = wp.data.withDispatch;
	var compose = wp.compose ? wp.compose.compose : wp.compose;

	var ServerSideRender = serverSideRender;

	var styleOptions = [];
	if (pixinlinkData && pixinlinkData.styles) {
		Object.keys(pixinlinkData.styles).forEach(function (value) {
			styleOptions.push({ value: value, label: pixinlinkData.styles[value] });
		});
	}

	var formatOptions = [];
	if (pixinlinkData && pixinlinkData.formats) {
		Object.keys(pixinlinkData.formats).forEach(function (value) {
			formatOptions.push({ value: value, label: pixinlinkData.formats[value] });
		});
	}

	registerBlockType('pixinlink/image', {
		title: 'PixInLink Image',
		description: 'Generate and insert an AI image using PixInLink.',
		icon: 'format-image',
		category: 'media',
		keywords: ['ai', 'image', 'generate', 'pixinlink'],
		supports: {
			align: ['wide', 'full'],
			html: false,
		},

		attributes: {
			prompt: {
				type: 'string',
				default: '',
			},
			width: {
				type: 'integer',
				default: pixinlinkData ? pixinlinkData.defaultWidth : 800,
			},
			height: {
				type: 'integer',
				default: pixinlinkData ? pixinlinkData.defaultHeight : 400,
			},
			style: {
				type: 'string',
				default: pixinlinkData ? pixinlinkData.defaultStyle : 'realistic',
			},
			format: {
				type: 'string',
				default: pixinlinkData ? pixinlinkData.defaultFormat : 'webp',
			},
			seed: {
				type: 'string',
				default: '',
			},
			imageUrl: {
				type: 'string',
				default: '',
			},
			imageId: {
				type: 'integer',
				default: 0,
			},
			altText: {
				type: 'string',
				default: '',
			},
		},

		edit: function (props) {
			var attributes = props.attributes;
			var setAttributes = props.setAttributes;
			var isSelected = props.isSelected;
			var generating = false;
			var errorMsg = '';

			var generateRef = { current: generating };
			var errorRef = { current: errorMsg };

			function doGenerate() {
				generateRef.current = true;
				errorRef.current = '';
				setAttributes({ imageUrl: '', imageId: 0 });

				var body = new FormData();
				body.append('action', 'pixinlink_generate');
				body.append('_wpnonce', pixinlinkData.nonce);
				body.append('prompt', attributes.prompt);
				body.append('width', attributes.width);
				body.append('height', attributes.height);
				body.append('style', attributes.style);
				body.append('format', attributes.format);
				body.append('seed', attributes.seed || '');

				fetch(pixinlinkData.ajaxUrl, {
					method: 'POST',
					body: body,
				})
					.then(function (res) { return res.json(); })
					.then(function (data) {
						generateRef.current = false;
						if (data.success) {
							setAttributes({
								imageUrl: data.data.url,
								imageId: data.data.attachment_id,
							});
						} else {
							errorRef.current = data.data.message || 'Unknown error';
						}
					})
					.catch(function (err) {
						generateRef.current = false;
						errorRef.current = err.message || 'Request failed';
					});
			}

			function doPreview() {
				var body = new FormData();
				body.append('action', 'pixinlink_preview');
				body.append('_wpnonce', pixinlinkData.nonce);
				body.append('prompt', attributes.prompt);
				body.append('width', attributes.width);
				body.append('height', attributes.height);
				body.append('style', attributes.style);
				body.append('format', attributes.format);
				body.append('seed', attributes.seed || '');

				fetch(pixinlinkData.ajaxUrl, {
					method: 'POST',
					body: body,
				})
					.then(function (res) { return res.json(); })
					.then(function (data) {
						if (data.success && data.data.url) {
							setAttributes({ imageUrl: data.data.url });
						}
					})
					.catch(function () {});
			}

			var controls = [];

			if (isSelected) {
				controls.push(
					el(
						InspectorControls,
						{ key: 'inspector' },
						el(
							PanelBody,
							{ title: 'Image Settings', initialOpen: true },
							el(TextareaControl, {
								label: 'Prompt',
								value: attributes.prompt,
								onChange: function (val) {
									setAttributes({ prompt: val });
								},
								help: 'Describe the image you want to generate.',
							}),
							el('div', { style: { display: 'flex', gap: '8px' } },
								el(TextControl, {
									label: 'Width',
									type: 'number',
									value: attributes.width,
									onChange: function (val) {
										setAttributes({ width: parseInt(val, 10) || 800 });
									},
									min: 1,
									max: 4096,
								}),
								el(TextControl, {
									label: 'Height',
									type: 'number',
									value: attributes.height,
									onChange: function (val) {
										setAttributes({ height: parseInt(val, 10) || 400 });
									},
									min: 1,
									max: 4096,
								})
							),
							el(SelectControl, {
								label: 'Style',
								value: attributes.style,
								options: styleOptions,
								onChange: function (val) {
									setAttributes({ style: val });
								},
							}),
							el(SelectControl, {
								label: 'Format',
								value: attributes.format,
								options: formatOptions,
								onChange: function (val) {
									setAttributes({ format: val });
								},
							}),
							el(TextControl, {
								label: 'Seed (optional)',
								type: 'number',
								value: attributes.seed,
								onChange: function (val) {
									setAttributes({ seed: val });
								},
								help: 'Use the same seed with the same prompt to reproduce an image.',
							}),
							el(TextControl, {
								label: 'Alt Text',
								value: attributes.altText,
								onChange: function (val) {
									setAttributes({ altText: val });
								},
							}),
							el(Button, {
								isPrimary: true,
								onClick: doGenerate,
								isBusy: generateRef.current,
								disabled: !attributes.prompt,
								style: { marginBottom: '8px', width: '100%', justifyContent: 'center' },
							}, 'Generate Image'),
							errorRef.current
								? el('p', { style: { color: 'red' } }, errorRef.current)
								: null
						)
					)
				);
			}

			if (!attributes.prompt && !attributes.imageUrl) {
				return el(
					'div',
					{},
					controls,
					el(
						Placeholder,
						{
							icon: 'format-image',
							label: 'PixInLink Image',
							instructions: 'Enter a prompt and settings in the block inspector panel, then click Generate.',
						}
					)
				);
			}

			return el(
				'div',
				{},
				controls,
				el(ServerSideRender, {
					block: 'pixinlink/image',
					attributes: attributes,
					httpMethod: 'POST',
				})
			);
		},

		save: function () {
			return null;
		},
	});
})(window.wp);
