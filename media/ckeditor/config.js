/**
 * @license Copyright (c) 2003-2015, CKSource - Frederico Knabben. All rights reserved.
 * For licensing, see LICENSE.md or http://ckeditor.com/license
 */

CKEDITOR.editorConfig = function(config) {
	config.toolbar = [
		['Undo', 'Redo'],
		['Bold', 'Italic', 'Underline', 'Strike', 'Subscript', 'Superscript'],
		['NumberedList', 'BulletedList', 'Outdent', 'Indent'],
		['JustifyLeft', 'JustifyCenter', 'JustifyRight', 'JustifyBlock'],
		['Link', 'Unlink'],
		['Image', 'Iframe'],
		['Blockquote', 'Table', 'HorizontalRule'],
		['Source']
	];
	
	config.removeDialogTabs = 'link:advanced;image:advanced;iframe:advanced;table:advanced';
};