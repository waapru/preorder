<?php

class shopPreorderPluginListAction extends waViewAction
{

	public function execute()
	{
		$offset = waRequest::get('offset',0,'int');
		
		$model = new shopPreorderPluginOrderModel;
		$list = $model->getList($offset);
		$count = $model->getCount();
		
		$offset += shopPreorderPluginOrderModel::PRODUCTS_PER_LOAD;
		$offset = ( $offset > $count ) ? $count : $offset;
		$current = $offset;
		
		$this->view->assign(compact('current','offset','list','count'));
	}

}