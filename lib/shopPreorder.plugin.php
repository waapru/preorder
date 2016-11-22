<?php

class shopPreorderPlugin extends shopPlugin
{
	const NO_EMAIL_ERROR = 1;
	const PREORDER_EXISTS = 2;
	const NOT_ZERO_COUNT = 3;
	const NO_PHONE_ERROR = 4;
	const PHONE_EMAIL_ERROR = 5;

	/* заглушка для старых версий */
	static public function on()
	{
		return false;
	}
	
	
	static public function form($product)
	{
		$html = '';
		$plugin = wa('shop')->getPlugin('preorder');
		$settings = $plugin->getSettings();
		
		$params = self::m('cp')->get($product['category_id']);
		$category_preorder = 1;
		if ( isset($params['preorder']) )
		{
			$params['preorder'] = (int)$params['preorder'];
			if ( $params['preorder'] != 1 )
				$category_preorder = 0;
		}
		
		if ( $category_preorder )
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
			
			$preorder_feature_exists = ( self::m('f')->countByField('code','preorder') ) ? true : false;
			$skus = self::m('ps')->getByField('product_id',$product['id'],true);
			foreach ( $skus as $sku )
			{
				$on = 1;
				if ( $preorder_feature_exists )
					if ( $row = self::m('pf')->getByField('sku_id', $sku['id']) )
						if ( $row = self::m('fvv')->getById($row['feature_value_id']) )
							$on = $row['value'];
				$data['product_ids'][$sku['id']] = $product['id'];
				if ( $on && isset($sku['count']) && $sku['count'] <= 0 && $sku['available'] )
					$data['skus'][$sku['id']] = $sku;
			}
			
			$view = wa()->getView();
			$view->assign(compact('data','settings'));
			
			$html = $view->fetch('string:'.self::f()->getFileContent('form'));
		}
		return $html;
	}
	
	
	public function frontendProduct($product)
	{
		return array(
			'cart' => $this->getSettings('hook') ? self::form($product) : ''
		);
	}
	
	
	public function frontendHead()
	{
		$settings = $this->getSettings();
		if ( $settings['css_on'] )
			self::f()->addCss('css');
		if ( $settings['js_on'] )
			self::f()->addJs('js');
		$view = wa()->getView();
		$view->assign('settings',$settings);
		return $view->fetch('string:'.self::f()->getFileContent('head'));
	}
	
	
	public function orderActionDelete()
	{
		$order_id = waRequest::post('id',0,waRequest::TYPE_INT);
		if ( self::m('ol')->getPreviousState($order_id) == 'preorder' )
			self::m('o')->reduceProductsFromStocks($order_id);
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
	
	
	static protected function f()
	{
		static $f;
		return isset($f) ? $f : new shopPreorderPluginFiles;
	}
	
	
	static protected function m($m = 'c')
	{
		static $models;
		$model_names = array(
			'cp' => 'shopCategoryParamsModel',
			'f' => 'shopFeatureModel',
			'pf' => 'shopProductFeaturesModel',
			'ol' => 'shopOrderLogModel',
			'ps' => 'shopProductSkusModel',
			'fvv' => 'shopFeatureValuesVarcharModel',
			'o' => 'shopOrderModel',
		);
		$m = isset($model_names[$m]) ? $m : 'c';
		if ( !isset($models[$m]) )
			$models[$m] = new $model_names[$m];
		return $models[$m];
	}

}