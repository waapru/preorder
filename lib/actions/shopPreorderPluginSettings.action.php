<?php

class shopPreorderPluginSettingsAction extends waViewAction
{

	public function execute()
	{
		$plugin_id = 'preorder';
		$path = 'plugins/'.$plugin_id.'/';
		
		$response = $this->getResponse();
		$response->addJs($path.'js/jquery.waapplugindisign.js','shop');
		$response->addCss($path.'css/jquery.waapplugindisign.css','shop');
		$response->addJs($path.'js/jquery.waapplugindesc.js','shop');
		$response->addCss($path.'css/jquery.waapplugindesc.css','shop');
		$this->view->assign('js',$response->getJs(true,true));
		$this->view->assign('css',$response->getCss(true,true));
		
		$f = new shopPreorderPluginFiles;
		$this->view->assign('themes',$f->getThemes());
		
		$plugin = wa()->getPlugin($plugin_id);
		$this->view->assign('settings',$plugin->getSettings());
		$this->view->assign('settingsHTML',$plugin->getSettingsHTML());
	}

}