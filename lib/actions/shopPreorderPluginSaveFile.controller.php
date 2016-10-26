<?php

class shopPreorderPluginSaveFileController extends waJsonController
{

	public function execute()
	{
		$theme = waRequest::post('theme','');
		$f = new shopPreorderPluginFiles($theme);
		$f->saveFromPostData();
	}

}