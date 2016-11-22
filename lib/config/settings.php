<?php

return array(
	'css_on' => array(
		'title' => 'Включить css',
		'description' => '',
		'control_type' => waHtmlControl::CHECKBOX,
		'value' => 1
	),
	'js_on' => array(
		'title' => 'Включить js',
		'description' => '',
		'control_type' => waHtmlControl::CHECKBOX,
		'value' => 1
	),
	'hook' => array(
		'title' => 'Включить вывод формы предзаказа через хук frontend_product.cart',
		'description' => 'Если флажок снят, то предложение о предзаказе не будет выводиться через хук frontend_product.cart.',
		'control_type' => waHtmlControl::CHECKBOX,
		'value' => 1
	),
	'done' => array(
		'title' => 'Сообщение об успешном предзаказе',
		'description' => 'Сообщение выводится после успешно выполненого предзаказа',
		'control_type' => waHtmlControl::INPUT,
		'value' => 'Предзаказ выполнен'
	),
	'just_done' => array(
		'title' => 'Сообщение об уже выполненном предзаказе данного товара',
		'description' => 'Сообщение выводится после попытки повторения предзаказа одного и того же товара одним пользователем',
		'control_type' => waHtmlControl::INPUT,
		'value' => 'Предзаказ уже выполнен'
	),
	'button' => array(
		'title' => 'Фраза на кнопке',
		'description' => 'Фраза на кнопке формы предзаказа',
		'control_type' => waHtmlControl::INPUT,
		'value' => 'Сделать предзаказ'
	),
	'btncolor' => array(
		'title' => 'Цвет кнопки',
		'description' => '',
		'control_type' => waHtmlControl::SELECT,
		'value' => 'green',
		'options' => array(
			'blue' => 'синий',
			'green' => 'зеленый',
			'sky' => 'голубой',
			'orange' => 'оранжевый',
			'red' => 'красный')
	),
	'modaltheme' => array(
		'title' => 'Тема модального окна',
		'description' => '',
		'control_type' => waHtmlControl::SELECT,
		'value' => 'green',
		'options' => array(
			'simple' => 'светлая',
			'dark' => 'темная',
		),
	),
	'email_error' => array(
		'title' => 'Сообщение о некорректном Email',
		'description' => 'Сообщение выводится после попытки предзаказа с указанным некорректным email или в случае, если email не указан.',
		'control_type' => waHtmlControl::INPUT,
		'value' => 'Email некорректен'
	),
	'phone_error' => array(
		'title' => 'Сообщение о некорректном телефонном номере',
		'description' => 'Сообщение выводится после попытки предзаказа с указанным некорректным телефонным номером или в случае, если номер не указан.',
		'control_type' => waHtmlControl::INPUT,
		'value' => 'Телефонный номер некорректен'
	),
	'email_or_phone_error' => array(
		'title' => 'Сообщение о пустом телефонном номере или email',
		'description' => 'Сообщение выводится после попытки предзаказа с незаполнеными полями телефонного номера или email.',
		'control_type' => waHtmlControl::INPUT,
		'value' => 'Заполните хотя бы одно из полей телефонный номер или email'
	),
	'type' => array(
		'title' => 'Основное поле',
		'description' => 'Выбор типа основной контактной информции: электронной почты или номера телефона контакта.',
		'control_type' => waHtmlControl::SELECT,
		'value' => 'email',
		'options' => array('email','phone','email_or_phone')
	),
	'name' => array(
		'title' => 'Запрашивать поле Имя',
		'description' => 'Переключатель определяет выводить поле "Имя" при оформлении предзаказа. Поле "Имя" не является обязательным.',
		'control_type' => waHtmlControl::CHECKBOX,
		'value' => 1
	),
	'comment' => array(
		'title' => 'Запрашивать поле Комментарий',
		'description' => 'Переключатель определяет выводить поле "Комментарий" при оформлении предзаказа. Поле "Комментарий" не является обязательным.',
		'control_type' => waHtmlControl::CHECKBOX,
		'value' => 1
	),
);