<?php

return array(
	'name' => 'Предзаказ',
	'description' => '',
	'vendor' => '929600',
	'version' => '1.2.400',
	'img' => 'img/preorder.png',
	'shop_settings' => true,
	'frontend' => true,
	'handlers' => array(
		'frontend_product' => 'frontendProduct',
		'frontend_head' => 'frontendHead',
		'order_action.delete' => 'orderActionDelete',
		'backend_products' => 'backendProducts',
	),
);
//EOF