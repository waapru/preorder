<?php

class shopPreorderPluginOrderModel extends waModel
{
	const PRODUCTS_PER_LOAD = 50;
	
	public function getList($offset = 0)
	{
		$list = array();
		$q = $this->listSql();
		if ( $r = $this->query($q,compact('offset'))->fetchAll() )
		{
			$product_ids = array();
			foreach ( $r as $v )
				if ( !in_array($v['product_id'],$product_ids) )
					$product_ids[] = $v['product_id'];
			
			if ( !empty($product_ids) )
			{
				$collection = new shopProductsCollection(array_values($product_ids));
				$products = $collection->getProducts('*,skus');
				if ( !empty($products) )
					foreach ( $r as $v )
					{
						extract($v);
						$product = $products[$product_id];
						$list[$sku_id] = compact('count','product');
					}
			}
		}
		
		return $list;
	}
	
	
	protected function listSql($limit = true)
	{
		return "
			SELECT
			  i.sku_id,
			  i.sku_code,
			  i.product_id,
			  SUM(i.quantity) AS count
			FROM shop_order_items i
			LEFT JOIN shop_order o
				ON i.order_id = o.id
			WHERE o.state_id LIKE 'preorder'
			GROUP BY i.sku_id
			ORDER BY i.product_id, i.sku_id ".($limit ? 'LIMIT i:offset, '.self::PRODUCTS_PER_LOAD : '');
	}
	
	
	public function getCount()
	{
		$q = 'SELECT COUNT(*) as count FROM ('.$this->listSql(false).') t';
		return $this->query($q)->fetchField('count');
	}
}