/*
Copyright (c) 2003-2011, CKSource - Frederico Knabben. All rights reserved.
For licensing, see LICENSE.html or http://ckeditor.com/license
*/

CKEDITOR.editorConfig = function( config )
{
	// Define changes to default configuration here. For example:
	config.language = 'ru';

    config.toolbar = 'Bagira';

    config.toolbar_Bagira =
    [
        ['Source','-','Undo','Redo'],
        ['Cut','Copy','Paste','PasteText','PasteFromWord','-','SelectAll','RemoveFormat'],
        ['Maximize', 'ShowBlocks','Preview','-','About'],
        '/',
         ['Format'],
        ['Bold','Italic','Underline','Strike','-','Subscript','Superscript'],
        ['NumberedList','BulletedList','-','Outdent','Indent','Blockquote'],
        ['JustifyLeft','JustifyCenter','JustifyRight'],
        ['BidiLtr', 'BidiRtl' ],
        ['Link','Unlink','Anchor'],
        ['Image','Table']
    ];
}
