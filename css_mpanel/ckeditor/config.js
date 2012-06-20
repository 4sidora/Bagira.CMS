/*
Copyright (c) 2003-2011, CKSource - Frederico Knabben. All rights reserved.
For licensing, see LICENSE.html or http://ckeditor.com/license
*/

CKEDITOR.editorConfig = function( config )
{
	// Define changes to default configuration here. For example:
	config.language = 'ru';

    config.toolbar = 'Bagira';

    config.extraPlugins = 'youtube,MediaEmbed';

    config.skin = 'chris';
    config.coreStyles_bold = { element : 'b', overrides : 'strong' };
    config.coreStyles_italic = { element : 'i', overrides : 'em' };

    config.pasteFromWordRemoveFontStyles = true;
    config.forceEnterMode = true;

    config.toolbar = [
        { name: 'document', items : [ 'Source','ShowBlocks','Print'] },
        { name: 'clipboard', items : [ 'Cut','Copy','PasteText','-','Undo','Redo' ] },
        { name: 'editing', items : [ 'Find','Replace','-','SelectAll' ] },
        { name: 'links', items : [ 'Link','Unlink' ] },
        { name: 'insert', items : [ '-','Image','MediaEmbed','Iframe','Flash','Table','Smiley','SpecialChar'] },
        { name: 'insert', items : ['-','About'] },

        '/',
        { name: 'styles', items : [ 'FontSize','Format' ] },    /* ,'Font' */
        { name: 'paragraph', items : [ '-','Bold','Italic','Underline','Strike'] },
        { name: 'paragraph', items : [ '-','JustifyLeft','JustifyCenter','JustifyRight','JustifyBlock' ] },
        { name: 'colors', items : [ 'TextColor','BGColor' ] },
        { name: 'basicstyles', items : [ '-','NumberedList','BulletedList','Subscript','Superscript'] },
        { name: 'basicstyles', items : [ '-','RemoveFormat'] }
    ];
}
