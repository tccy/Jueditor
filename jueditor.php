<?php
defined('_JEXEC') or die;
class plgEditorjueditor extends JPlugin
{
    function plgEditorjueditor( &$subject, $config )
    {
        parent::__construct( $subject, $config );
    }
	
	public function onInit()
	{
		$txt = '<script type="text/javascript">window.UEDITOR_HOME_URL="'.JURI::root().'plugins/editors/jueditor/ueditor/";</script>';
		$txt .= '<meta http-equiv="Content-Type" content="text/html;charset=utf-8"/>';
  		$txt .= '<script type="text/javascript" charset="utf-8" src="'.JURI::root().'plugins/editors/jueditor/ueditor/editor_config.js"></script>';
		$txt .= '<script type="text/javascript" charset="utf-8" src="'.JURI::root().'plugins/editors/jueditor/ueditor/editor_all.js"></script>';
		return $txt;
	}
	
	function onSave($id)
	{
		return 'document.getElementById("'.$id.'").value = ue.getContent();';
	}

	function onGetContent(){return;}

	function onSetContent(){return;}

	function onGetInsertMethod($id)
	{
		static $done = false;

		// Do this only once.
		if (!$done) {
			$doc = JFactory::getDocument();
			$js = "\tfunction jInsertEditorText(text, editor) {
				insertAtCursor(document.getElementById(editor), text);
			}";
			$doc->addScriptDeclaration($js);
		}

		return true;
	}

	function onDisplay($name, $content, $width, $height, $col, $row, $buttons = true, $id = null, $asset = null, $author = null, $params = array())
	{
		if (empty($id)) {$id = $name;}
	//	Only add "px" to width and height if they are not given as a percentage
		if (is_numeric($width)) {$width .= 'px';}
		if (is_numeric($height)) {$height .= 'px';}

		$buttons = $this->_displayButtons($id, $buttons, $asset, $author);
		$editor  = "<textarea name=\"$name\" id=\"$id\" cols=\"$col\" rows=\"$row\" style=\"width: $width; height: $height;\">$content</textarea>" . $buttons;

		$editor .= '<script type="text/javascript">';
		$editor .= 'var text = document.getElementById("'.$id.'");';
	//	实例化编辑器	
	//	$editor .= 'var ue = UE.getEditor("'.$id.'");';
	//	功能配置
		$editor .= 'var ue = UE.getEditor("'.$id.'",{';
		$editor .= 'toolbars:[["fullscreen", "source", "|",';

		if($this->params->get('undo_redo', 1)){
			$editor .= '"undo", "redo", "|",';}

		if($this->params->get('text_format', 1)){
			$editor .= '"bold", "italic", "underline", "strikethrough", "superscript", "subscript", "|",';}

		if($this->params->get('list', 1)){
			$editor .= '"insertorderedlist", "insertunorderedlist", "|",';}

		if($this->params->get('link', 1)){
			$editor .= '"link", "unlink", "|",';}

		if($this->params->get('clear_selectall', 1)){
			$editor .= '"cleardoc", "selectall", "|",';}

		if($this->params->get('font_format', 1)){
			$editor .= '"fontfamily", "fontsize", "forecolor", "backcolor", "|", "removeformat", "formatmatch", "|",';}

		if($this->params->get('justify', 1)){
			$editor .= '"justifyleft", "justifycenter", "justifyright", "justifyjustify", "|",';}

		if($this->params->get('image', 1)){
			$editor .= '"imagenone", "imageleft", "imageright", "imagecenter", "|",';}

		if($this->params->get('insert_std', 1)){
			$editor .= '"insertimage", "scrawl", "music", "attachment", "snapscreen", "emotion", "insertvideo", "|",';}

		if($this->params->get('insert_spc', 1)){
			$editor .= '"spechars", "blockquote", "highlightcode", "date", "time", "|",';}

		if($this->params->get('insert_typesetting', 1)){
			$editor .= '"pagebreak", "wordimage", "horizontal", "anchor", "insertframe", "template", "background", "|",';}

		if($this->params->get('typesetting', 1)){
			$editor .= '"indent", "autotypeset", "pasteplain", "customstyle", "paragraph", "rowspacingtop", "rowspacingbottom", "lineheight", "|",';}

		if($this->params->get('table', 1)){
			$editor .= '"inserttable", "deletetable", "insertparagraphbeforetable", "insertrow", "deleterow", "insertcol", "deletecol", "mergecells", "mergeright", "mergedown", "splittocells", "splittorows", "splittocols", "|",';}

		if($this->params->get('insert_app', 1)){
			$editor .= '"gmap", "map", "webapp", "|",';}

		if($this->params->get('Letter', 1)){
			$editor .= '"touppercase", "tolowercase", "|",';}

		if($this->params->get('Input_direction', 1)){
			$editor .= '"directionalityltr", "directionalityrtl", "|",';}

		$editor .= '"print", "searchreplace", "preview", "help"]],';
		if($this->params->get('language', 1)){$editor .= 'lang:"zh-cn"';}
		else{$editor .= 'lang:"en"';}
		$editor .= '});';
		$editor .= '</script>';
		return $editor;
	}

	function _displayButtons($name, $buttons, $asset, $author)
	{
		// Load modal popup behavior
		JHtml::_('behavior.modal', 'a.modal-button');

		$args['name'] = $name;
		$args['event'] = 'onGetInsertMethod';

		$return = '';
		$results[] = $this->update($args);

		foreach ($results as $result)
		{
			if (is_string($result) && trim($result)) {
				$return .= $result;
			}
		}

		if (is_array($buttons) || (is_bool($buttons) && $buttons)) {
			$results = $this->_subject->getButtons($name, $buttons, $asset, $author);

			// This will allow plugins to attach buttons or change the behavior on the fly using AJAX
			$return .= "\n<div id=\"editor-xtd-buttons\">\n";

			foreach ($results as $button)
			{
				// Results should be an object
				if ($button->get('name')) {
					$modal		= ($button->get('modal')) ? 'class="modal-button"' : null;
					$href		= ($button->get('link')) ? 'href="'.JURI::base().$button->get('link').'"' : null;
					$onclick	= ($button->get('onclick')) ? 'onclick="'.$button->get('onclick').'"' : null;
					$title      = ($button->get('title')) ? $button->get('title') : $button->get('text');
					$return .= "<div class=\"button2-left\"><div class=\"".$button->get('name')."\"><a ".$modal." title=\"".$title."\" ".$href." ".$onclick." rel=\"".$button->get('options')."\">".$button->get('text')."</a></div></div>\n";
				}
			}

			$return .= "</div>\n";
		}

		return $return;
	}
}
?>