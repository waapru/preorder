<?php

class shopPreorderPluginGetFileContentController extends waJsonController
{

	public function execute()
	{
		$theme = waRequest::post('theme','');
		$name = waRequest::post('name','');
		
		$f = new shopPreorderPluginFiles($theme);
		$this->response = $f->getFileContent($name);
	}

}