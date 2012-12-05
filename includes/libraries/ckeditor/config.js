/*
Copyright (c) 2003-2011, CKSource - Frederico Knabben. All rights reserved.
For licensing, see LICENSE.html or http://ckeditor.com/license
*/

CKEDITOR.editorConfig = function( config )
{
	// Define changes to default configuration here. For example:
	// config.language = 'fr';
	// config.uiColor = '#AADC6E';
	config.resize_enabled = false; 
	config.toolbar = 'Basic'; 
	config.toolbar_Basic = [
      ['Source'],
      ['Checkbox', 'Radio', 'TextField'],
      ['Bold','Italic','Underline','Strike','-','Subscript','Superscript'],
      ['NumberedList','BulletedList'],
      ['JustifyLeft','JustifyCenter','JustifyRight','JustifyBlock'],
      ['Styles','Format','Font','FontSize'],
      ['TextColor','BGColor']
  ]; 
};
