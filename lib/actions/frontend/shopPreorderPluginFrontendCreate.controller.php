<?php

class shopPreorderPluginFrontendCreateController extends waJsonController
{
	protected $_sku_data = array();
	protected $_product_data = array();
	protected $_product_id = 0;
	protected $_sku_id = 0;
	protected $_order_id = 0;
	protected $_email = '';
	protected $_phone = '';
	protected $_quantity = '';
	protected $_post_comment = array(
		'text' => '',
		'fields' => array(),
	);
	
	public function execute()
	{
		if ( !waRequest::isXMLHttpRequest() )
			throw new waException('Page not found', 404);
		
		$plugin = wa('shop')->getPlugin('preorder');
		$settings = $plugin->getSettings();
		$type = $settings['type'];
		
		$this->_product_id = waRequest::post('product_id', 0, waRequest::TYPE_INT);
		$this->_sku_id = waRequest::post('sku_id', 0, waRequest::TYPE_INT);
		$this->_quantity = waRequest::post('quantity', 1, waRequest::TYPE_INT);
		
		if ( $this->_sku_id == 0 && $this->_product_id > 0 )
			$this->_initByProductId()->_initBySkuId();
		elseif ( $this->_sku_id > 0 )
			$this->_initBySkuId()->_initByProductId();
		
		$name = waRequest::post('name','',waRequest::TYPE_STRING_TRIM);
		$email = waRequest::post('email','',waRequest::TYPE_STRING_TRIM);
		$this->_post_comment['text'] = waRequest::post('comment', '', waRequest::TYPE_STRING_TRIM);
		$phone = waRequest::post('phone','',waRequest::TYPE_STRING_TRIM);
		$phone = $this->_clearPhoneNumber($phone);
		
		$contact = $this->getUser()->isAuth() ? $this->getUser() : 0;
		$contact_id = ($contact) ? $contact->get('id') : 0;
		
		$this->_post_comment['text'] = str_replace('---','--',$this->_post_comment['text']);
		$post_data = waRequest::post();
		if ( !empty($post_data) )
			foreach ( $post_data as $k=>$v )
			{
				$v = trim($v);
				
				if ( !empty($v) && preg_match('/add_(.+)/i',$k,$m) )
					$this->_post_comment['fields'][] = array(
						'name' => $m[1],
						'value' => str_replace(';',',',$v),
					);
			}
		if ( !empty($name) )
			$this->_post_comment['fields'][] = array(
				'name' => 'Покупатель',
				'value' => $name,
			);
		
		$error = 0;
		$valid = 0;
		if ( !empty($email) && ($type == 0 || $type == 2) )
		{
			$email_validator = new waEmailValidator;
			$valid = $email_validator->isValid($email) ? 1 : 0;
			
			if ( $contact_id == 0 && $valid )
			{
				$contact_id = $this->_getContactIdByEmail($email);
				if ( $contact_id == 0 )
				{
					$contact_id = $this->_createContact($name,$email);
					if ( $contact_id == 0 )
						$this->_post_comment['fields'][] = array(
							'name' => 'Email',
							'value' => $email,
						);
				}
			}
		}

		if ( !empty($phone) && ($type == 1 || $type == 2) )
		{
			if ( $contact_id == 0 )
			{
				$contact_id = $this->_getContactIdByPhone($phone);
				if ( $contact_id == 0 )
				{
					$contact_id = $this->_createContact($name,'',$phone);
					if ( $contact_id == 0 )
						if ( $contact_id == 0 )
							$this->_post_comment['fields'][] = array(
								'name' => 'Телефон',
								'value' => $phone,
							);
				}
			}
			else
				$this->_updateContactPhoneNumber($phone,$contact_id);
		}

		if ( $type == 2 && empty($email) && empty($phone) )
			$error = shopPreorderPlugin::PHONE_EMAIL_ERROR;
		elseif ( $type == 0 && !$valid && !$contact)
			$error = shopPreorderPlugin::NO_EMAIL_ERROR;
		elseif ( $type == 1 && empty($phone) )
			$error = shopPreorderPlugin::NO_PHONE_ERROR;
		
		
		$data = array();
		if ( !empty($this->_product_data) && !empty($this->_sku_data) )
		{
			if ( !isset($this->_sku_data['count']) || $this->_sku_data['count']>0 )
				$error = shopPreorderPlugin::NOT_ZERO_COUNT;
			if ( !$error )
			{
				$data = array_merge($this->_product_data,$this->_sku_data);
				$data['contact_id'] = $contact_id;
				$this->_email = isset($email) ? $email : '';
				$this->_phone = isset($phone) ? $phone : '';
				if ( $this->_preorderExists($data) )
					$error = shopPreorderPlugin::PREORDER_EXISTS;
				else
					$this->_createOrder($data);
			}
		}
		$this->response['error'] = $error;
	}
	
	
	protected function _updateContactPhoneNumber($phone,$contact_id)
	{
		if ( $contact_id && !empty($phone) )
		{
			$model = new waContactDataModel;
			$fields = array(
				'contact_id' => (int)$contact_id,
				'field' => 'phone',
			);
			if ( $data = $model->getByField($fields) )
				$model->updateById($data['id'],array('value'=>$phone));
			else
				$model->addField($contact_id, 'phone', $phone);
		}
	}
	
	
	protected function _clearPhoneNumber($phone)
	{
		return preg_replace('/[^0-9]/i','',$phone);
	}
	
	
	protected function _createContact($name='',$email='',$phone='')
	{
		$contact_id = 0;
		if ( !empty($email) || !empty($phone) )
		{
			$data = array(
				'login' => (empty($email)) ? $phone : $email,
				'name' => (!empty($name)) ? $name : 'Покупатель',
				'create_method' => 'preorder',
				'create_ip' => waRequest::getIp(),
				'create_user_agent' => waRequest::getUserAgent()
			);
			
			if ( !empty($email) )
				$data['email'] = array('value' => $email, 'status' => 'unconfirmed');
			
			$contact = new waContact();
			if ( !$errors = $contact->save($data, true) )
				$contact_id = $contact->get('id');
			
			if ( $contact_id && !empty($phone) )
			{
				$model = new waContactDataModel;
				$model->addField($contact_id, 'phone', $phone);
			}
		}
		return $contact_id;
	}
	
	
	protected function _getContactIdByEmail($email)
	{
		$model = new waContactEmailsModel;
		$contact_id = 0;
		if ( $r = $model->getByField('email',$email) )
			$contact_id = $r['contact_id'];
		return $contact_id;
	}
	
	
	protected function _getContactIdByPhone($phone)
	{
		$contact_id = 0;
		$data = array(
			'field' => 'phone',
			'value' => $phone,
		);
		$model = new waContactDataModel;
		if ( $r = $model->getByField($data) )
			$contact_id = $r['contact_id'];
		return $contact_id;
	}
	
	
	protected function _createOrder($data)
	{
		$order_model = new shopOrderModel;
		
		if ( $this->_order_id )
		{
			$order_id = $this->_order_id;
			$update_data = $this->_getUpdateOrderData($data);
			if ( !empty($update_data) )
				$order_model->updateById($order_id,$update_data);
		}
		else
			$order_id = $order_model->insert($this->_getOrderData($data));
		
		if ( $order_id > 0 )
		{
			$order_items_model = new shopOrderItemsModel;
			$order_items_model->insert($this->_getOrderItemData($data,$order_id));
			
			$params['auth_code'] = shopWorkflowCreateAction::generateAuthCode($order_id);
			$params['auth_pin'] = shopWorkflowCreateAction::generateAuthPin();

			$params_model = new shopOrderParamsModel();
			$params_model->set($order_id, $params);
			
			$this->_sendNotification($order_id);
		}
		
		return $order_id;
	}
	
	
	protected function _sendNotification($order_id)
	{
		$data['order_id'] = $order_id;
		$data['action_id'] = '';

		$data['before_state_id'] = '';
		$data['after_state_id'] = 'preorder';
		
		$order_model = new shopOrderModel();
		$order = $order_model->getById($order_id);
		shopNotifications::send('order.preorder_create', array(
			'order' => $order,
			'customer' => new waContact($order['contact_id']),
			'status' => 'Предварительный заказ',
			'action_data' => $data
		));
	}
	
	
	protected function _initByProductId()
	{
		$data = array();
		if ( $this->_product_id > 0 )
		{
			$model = new shopProductModel;
			if ( $r = $model->getByField('id',$this->_product_id) )
			{
				$this->_product_data = array(
					'name' => $r['name'],
					'currency' => $r['currency'],
				);
				if ( !$this->_sku_id )
					$this->_sku_id = $r['sku_id'];
			}
		}
		return $this;
	}
	
	
	protected function _initBySkuId()
	{
		$data = array();
		if ( $this->_sku_id > 0 )
		{
			$model = new shopProductSkusModel;
			if ( $r = $model->getByField('id',$this->_sku_id) )
			{
				$this->_sku_data = array(
					'sku' => $r['sku'],
					'sku_name' => $r['name'],
					'count' => $r['count'],
					'price' => $r['price'],
				);
				$this->_product_id = $r['product_id'];
			}
		}
		return $this;
	}
	
	
	protected function _getOrderData($data)
	{
		$config = wa('shop')->getConfig();
		$primary = $config->getCurrency(true);
		return array(
			'contact_id' => $data['contact_id'],
			'create_datetime' => date('Y-m-d H:i:s',time()),
			'state_id' => 'preorder',
			'total' => shop_currency($data['price']*$this->_quantity,$data['currency'],null,false),
			'currency' => $primary,
			'rate' => 1,
			'comment' => $this->_getComment()
		);
	}
	
	
	protected function _getComment($comment = '')
	{
		$text = '';
		$fields = array();
		if ( !empty($comment) )
		{
			$a = explode('---',$comment);
			$text = trim($a[0]); 
			if ( count($a) > 1 )
			{
				unset($a[0]);
				$a = trim(implode('---',$a));
				foreach ( array_map('trim', explode(';',$a) ) as $v )
				{
					$c = explode(':',$v);
					if ( count($c) > 1 )
					{
						$name = trim($c[0]);
						unset($c[0]);
						$value = trim(implode(':',$c));
						$fields[$name] = $value;
					}
				}
			}
		}
		if ( !empty($text) )
			$text .= "\r\n-\r\n";
		$comment = $text . $this->_post_comment['text'] . "\r\n---\r\n";
		foreach ( $this->_post_comment['fields'] as $field )
		{
			unset($fields[$field['name']]);
			$comment .= $field['name'].': '.$field['value'].";\r\n";
		}
		if ( !empty($fields) )
			foreach ( $fields as $name=>$value )
				$comment .= $name.': '.$value.";\r\n";
		
		if ( trim($comment) == '---' )
			$comment = '';
		return $comment;
	}
	
	
	protected function _getUpdateOrderData($data)
	{
		$update_data = array();
		if ( $this->_order_id )
		{
			
			$order_model = new shopOrderModel;
			if ( $order = $order_model->getById($this->_order_id) )
				$update_data = array(
					'create_datetime' => date('Y-m-d H:i:s',time()),
					'total' => shop_currency($data['price']*$this->_quantity,$data['currency'],null,false) + $order['total'],
					'comment' => $this->_getComment($order['comment']),
				);
		}
		return $update_data;
	}
	
	
	protected function _getOrderItemData($data,$order_id)
	{
		$name = $data['name'];
		$name .= ( !empty($this->_sku_data['sku_name']) ) ? ' ('.$this->_sku_data['sku_name'].')' : '';
		return array(
			'order_id' => $order_id,
			'name' => $name,
			'product_id' => $this->_product_id,
			'sku_id' => $this->_sku_id,
			'sku_code' => $this->_sku_data['sku'],
			'type' => 'product',
			'price' => shop_currency($data['price'],$data['currency'],null,false),
			'quantity' => $this->_quantity,
		);
	}
	
	
	protected function _preorderExists($data)
	{
		$is = false;
		$order_model = new shopOrderModel;
		
		$where = '';
		if ( $data['contact_id'] > 0 )
			$where = "state_id='preorder' AND contact_id=".(int)$data['contact_id'];
		elseif ( !empty($this->_email) )
			$where = "comment LIKE '%".$order_model->escape($this->_email)."%' AND contact_id = 0";
		
		$this->_order_id = $order_model->select('id')->where($where)->fetchField('id');
		
		if ( $this->_order_id )
		{
			$order_items_model = new shopOrderItemsModel;
			$where = "order_id = {$this->_order_id} AND sku_id=".(int)$this->_sku_id;
			if ( $order_items_model->select('COUNT(*) as c')->where($where)->fetchField() > 0 )
				$is = true;
		}
		
		return $is;
	}
}