<?php

class shopPreorderPlugin extends shopPlugin
{
	const NO_EMAIL_ERROR = 1;
	const PREORDER_EXISTS = 2;
	const NOT_ZERO_COUNT = 3;
	const NO_PHONE_ERROR = 4;
	const PHONE_EMAIL_ERROR = 5;

	static public function on()
	{
		return wa('shop')->getPlugin('preorder')->getSettings('on');
	}
	
	
	static public function form($product)
	{
		$html = '';
		$plugin = wa('shop')->getPlugin('preorder');
		$settings = $plugin->getSettings();
		
		$category_params_model = new shopCategoryParamsModel;
		$params = $category_params_model->get($product['category_id']);
		$category_preorder = 1;
		if ( isset($params['preorder']) )
		{
			$params['preorder'] = (int)$params['preorder'];
			if ( $params['preorder'] != 1 )
				$category_preorder = 0;
		}
		
		if ( $settings['on'] && $category_preorder )
		{
			$data = array(
				'product_id' => $product['id'],
				'product_name' => $product['name'],
				'skus' => array(),
				'is_auth' => wa()->getUser()->isAuth() ? 1 : 0,
				'url' => wa('shop')->getAppUrl(),
				'product_ids' => array(),
			);
			$data['user_name'] = ( $data['is_auth'] ) ? wa()->getUser()->getName() : '';
			
			$feature_model = new shopFeatureModel;
			$preorder_feature_exists = ( $feature_model->countByField('code','preorder') ) ? true : false;
			
			if ( $preorder_feature_exists )
			{
				$product_features_model = new shopProductFeaturesModel;
				$feature_values_varchar_model = new shopFeatureValuesVarcharModel;
			}
			
			$model = new shopProductSkusModel;
			$skus = $model->getByField('product_id',$product['id'],true);
			foreach ( $skus as $sku )
			{
				$on = 1;
				if ( $preorder_feature_exists )
					if ( $row = $product_features_model->getByField('sku_id', $sku['id']) )
						if ( $row = $feature_values_varchar_model->getById($row['feature_value_id']) )
							$on = $row['value'];
				$data['product_ids'][$sku['id']] = $product['id'];
				if ( $on && isset($sku['count']) && $sku['count'] <= 0 && $sku['available'] )
					$data['skus'][$sku['id']] = $sku;
			}
			
			$view = wa()->getView();
			$view->assign('data',$data);
			$view->assign('product',$product);
			$view->assign('settings',$settings);
			
			$f = new shopPreorderPluginFiles;
			$html = $view->fetch('string:'.$f->getFileContent('form'));
		}
		return $html;
	}
	
	
	public function frontendProduct($product)
	{
		$html = '';
		
		$settings = $this->getSettings();
		if ( $settings['on'] && $settings['hook'] )
			$html = self::form($product);
		
		return array('cart'=>$html);
	}
	
	
	public function frontendHead()
	{
		$html = '';
		$settings = $this->getSettings();
		if ( $settings['on'] )
		{
			$response = waSystem::getInstance()->getResponse();
			
			$aurl = 'plugins/preorder/js/arcticmodal/';
			$response->addCss($aurl.'jquery.arcticmodal-0.3.css','shop');
			$response->addCss($aurl.'themes/simple.css','shop');
			$response->addJs($aurl.'jquery.arcticmodal-0.3.min.js','shop');
			$response->addJs('plugins/preorder/js/jquery.inputmask.js','shop');
			
			$f = new shopPreorderPluginFiles;
			$f->addCss('css');
			$f->addJs('js');
			
			$view = wa()->getView();
			$view->assign('settings',$settings);
			$html = $view->fetch('string:'.$f->getFileContent('head'));
		}
		return $html;
	}
	
	
	public function orderActionDelete()
	{
		$order_id = waRequest::post('id',0,waRequest::TYPE_INT);
		$log_model = new shopOrderLogModel;
		$state = $log_model->getPreviousState($order_id);
		if ( $state == 'preorder' )
		{
			$order_model = new shopOrderModel;
			$order_model->reduceProductsFromStocks($order_id);
		}
	}
	
	
	// event: backend_products
	public function backendProducts()
	{
		//$model = new shopFavPluginSkusModel;
		$count = 0;
		return array(
			'sidebar_top_li' => '<li id="s-action-preorder"><span class="count">'.$count.'</span><a href="#/preorderProducts/"><i class="icon16" style="background-image: url(\''.$this->getPluginStaticUrl().'img/preorder.png\');"></i>Предзаказанные товары</a>
			<script src="'.$this->getPluginStaticUrl().'js/products.js?v'.$this->info['version'].'"></script>
			</li>'
		);
	}
	
	
	public function getSettingsHTML($params = array())
	{
		$controls = array();
		$default = array(
			'instance'            => & $this,
			'title_wrapper'       => '%s',
			'description_wrapper' => '<br><span class="hint">%s</span>',
			'translate'           => array(&$this, '_w'),
			'control_wrapper'     => '
<div class="field">
	<div class="name">%s</div>
	<div class="value">%s%s</div>
</div>
',
		);
		$options = ifempty($params['options'], array());
		unset($params['options']);
		$params = array_merge($default, $params);

		foreach ($this->getSettingsConfig() as $name => $row) {
			$row = array_merge($row, $params);
			$row['value'] = $this->getSettings($name);
			if (isset($options[$name])) {
				$row['options'] = $options[$name];
			}
			if (isset($params['value']) && isset($params['value'][$name])) {
				$row['value'] = $params['value'][$name];
			}
			if (!empty($row['control_type'])) {
				$controls[$name] = waHtmlControl::getControl($row['control_type'], "shop_preorder[$name]", $row);
			}
		}
		return implode("\n", $controls);
	}

}