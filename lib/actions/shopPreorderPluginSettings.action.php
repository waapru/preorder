<?php

class shopPreorderPluginSettingsAction extends waViewAction
{

	public function execute()
	{
		$plugin_id = 'preorder';
		$plugin = wa()->getPlugin($plugin_id);
		
		$settings = array();
		foreach ( array('standart') as $t )
		{
			$controls = array(
				'subject' => $t,
				'namespace' => 'shop_'.$plugin_id,
				'title_wrapper' => '%s',
				'description_wrapper' => '<br><span class="hint">%s</span>',
				'control_wrapper'     => '<div class="field"><div class="name">%s</div><div class="value">%s%s</div></div>',
			);
			$settings[$t] = implode('',$plugin->getControls($controls));
		}
		
		$f = new shopPreorderPluginFiles;
		$themes = $f->getThemes();
		
		$v = $plugin->getVersion();
		$this->view->assign(compact('settings','v'));
	}

}