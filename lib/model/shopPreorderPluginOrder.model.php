<?php

class shopPreorderPluginOrderModel extends waModel
{
	const PRODUCTS_PER_LOAD = 30;
	
	public function getList($offset = 0)
	{
		$list = array();
		$q = "
			SELECT
			  i.sku_id,
			  i.sku_code,
			  i.product_id,
			  COUNT(i.sku_id) AS count
			FROM shop_order_items i
			  INNER JOIN shop_order o
				ON i.order_id = o.id
			WHERE o.state_id LIKE 'preorder'
			GROUP BY i.sku_id
			ORDER BY count DESC
			LIMIT $offset, ".self::PRODUCTS_PER_LOAD;
		
		if ( $r = $this->query($q)->fetchAll() )
		{
			$product_ids = array();
			foreach ( $r as $v )
				if ( !in_array($v['product_id'],$product_ids) )
					$product_ids[] = $v['product_id'];
			
			if ( !empty($product_ids) )
			{
				$collection = new shopProductsCollection(array_values($product_ids));
				$products = $collection->getProducts();
				if ( !empty($products) )
					foreach ( $r as $v )
					{
						extract($v);
						$name = $products[$product_id]['name'];
						$list[$sku_id] = compact('sku_code','count','name','product_id');
					}
			}
		}
		
		return $list;
	}
	
	
	public function getCount()
	{
		$q = "
			SELECT
			  COUNT(SubQuery.count) AS count
			FROM (SELECT
				COUNT(i.sku_id) AS count,
				i.sku_id
			  FROM shop_order_items i
				INNER JOIN shop_order o
				  ON i.order_id = o.id
			  WHERE o.state_id LIKE 'preorder'
			  GROUP BY i.sku_id) SubQuery
		";
		return $this->query($q)->fetchField('count');
	}
}