<?php
/**
 * Banners
 *
 * Система показа баннеров.
 *
 * @version: 2.03
 *
 * @copyright 2005, ProCreat Systems, http://procreat.ru/
 * @copyright 2007, Eresus Group, http://eresus.ru/
 * @license http://www.gnu.org/licenses/gpl.txt  GPL License 3
 * @author Михаил Красильников <mihalych@vsepofigu.ru>
 * @author БерсЪ <bersz@procreat.ru>
 * @author dkDimon <dkdimon@mail.ru>
 * @author ghost
 *
 * Данная программа является свободным программным обеспечением. Вы
 * вправе распространять ее и/или модифицировать в соответствии с
 * условиями версии 3 либо по вашему выбору с условиями более поздней
 * версии Стандартной Общественной Лицензии GNU, опубликованной Free
 * Software Foundation.
 *
 * Мы распространяем эту программу в надежде на то, что она будет вам
 * полезной, однако НЕ ПРЕДОСТАВЛЯЕМ НА НЕЕ НИКАКИХ ГАРАНТИЙ, в том
 * числе ГАРАНТИИ ТОВАРНОГО СОСТОЯНИЯ ПРИ ПРОДАЖЕ и ПРИГОДНОСТИ ДЛЯ
 * ИСПОЛЬЗОВАНИЯ В КОНКРЕТНЫХ ЦЕЛЯХ. Для получения более подробной
 * информации ознакомьтесь со Стандартной Общественной Лицензией GNU.
 *
 * Вы должны были получить копию Стандартной Общественной Лицензии
 * GNU с этой программой. Если Вы ее не получили, смотрите документ на
 * <http://www.gnu.org/licenses/>
 *
 * @package Banners
 *
 * $Id$
 */

/**
 * Класс плагина
 *
 * @package Banners
 */
class Banners extends Plugin
{
	/**
	 * Требуемая версия ядра
	 * @var string
	 */
	public $kernel = '2.15';

	/**
	 * Название плагина
	 * @var string
	 */
	public $title = 'Баннеры';

	/**
	 * Тип
	 * @var string
	 */
	public $type = 'client,admin';

	/**
	 * Версия
	 * @var string
	 */
	public $version = '2.03a';

	/**
	 * Описание
	 * @var string
	 */
	public $description = 'Система показа баннеров';

	/**
	 * Таблица АИ
	 * @var array
	 */
	private $table = array (
		'name' => 'banners',
		'key'=> 'id',
		'sortMode' => 'id',
		'sortDesc' => false,
		'columns' => array(
			array('name' => 'caption', 'caption' => 'Название'),
			array('name' => 'block', 'caption' => 'Блок', 'align'=> 'right'),
			array('name' => 'priority',
				'caption' => '<span title="Приоритет" style="cursor: default;">&nbsp;&nbsp;*</span>',
				'align'=>'center'),
			array('name' => 'showTill', 'caption' => 'До даты',
				'replace'=> array('0000-00-00'=> 'без огранич.')),
			array('name' => 'showCount', 'caption' => 'Макс.показ.', 'align'=>'right',
				'replace' => array('0'=> 'без огранич.')),
			array('name' => 'shows', 'caption' => 'Показан', 'align'=>'right'),
			array('name' => 'clicks', 'caption' => 'Кликов', 'align'=>'right'),
			//array('name' => 'mail', 'caption' => 'Владелец',
			//'value' => '<a href="mailto:$(mail)">$(mail)</a>', 'macros' => true),
		),
		'controls' => array (
			'delete' => '',
			'edit' => '',
			'toggle' => '',
		),
		'tabs' => array(
			'width'=>'180px',
			'items'=>array(
				array('caption'=>'Добавить баннер', 'name'=>'action', 'value'=>'create')
			),
		),
		'sql' => "(
			`id` int(10) unsigned NOT NULL auto_increment,
			`caption` varchar(255) default NULL,
			`active` tinyint(1) unsigned default NULL,
			`section` varchar(255) default NULL,
			`priority` int(10) unsigned default NULL,
			`block` varchar(31) default NULL,
			`showFrom` date default NULL,
			`showTill` date default NULL,
			`showCount` int(10) unsigned default NULL,
			`html` text,
			`image` varchar(255) default NULL,
			`width` varchar(15) default NULL,
			`height` varchar(15) default NULL,
			`url` varchar(255) default NULL,
			`target` tinyint(1) unsigned default NULL,
			`shows` bigint(20) unsigned default NULL,
			`clicks` bigint(20) unsigned default NULL,
			PRIMARY KEY	(`id`),
			KEY `active` (`active`),
			KEY `priority` (`priority`),
			KEY `showFrom` (`showFrom`),
			KEY `showTill` (`showTill`),
			KEY `showCount` (`showCount`),
			KEY `shows` (`shows`)
		) TYPE=MyISAM COMMENT='Banner system';",
	);

	/**
	 * Конструктор
	 *
	 * Производит регистрацию обработчиков событий.
	 */
	function __construct()
	{
		parent::__construct();

		if (defined('CLIENTUI'))
		{
			$this->listenEvents('clientOnPageRender');
		}
		else
		{
			$this->listenEvents('adminOnMenuRender');
		}
	}
	//-----------------------------------------------------------------------------

	/**
	 * Возвращает путь к директории данныз плагина
	 *
	 * @return string
	 *
	 * @since 2.00
	 */
	public function getDataDir()
	{
		return $this->dirData;
	}
	//-----------------------------------------------------------------------------

	/**
	 * Действия при установке плагина
	 */
	function install()
	{
		parent::install();

		$this->createTable($this->table);

		$this->mkdir();
	}
	//-----------------------------------------------------------------------------

	/**
	 * Возвращает ветку разделов сайта
	 *
	 * @param int $owner[optional]
	 * @param int $level[optional]
	 * @return array
	 */
	private function menuBranch($owner = 0, $level = 0)
	{
		global $Eresus;

		$result = array(array(), array());
		$items = $Eresus->db->select('pages',
			"(`access` >= '" . USER . "') AND (`owner` = '" . $owner . "') AND (`active` = '1')",
			"-position", "`id`,`caption`");
		if (count($items))
		{
			foreach ($items as $item)
			{
				$result[0][] = str_repeat('- ', $level).$item['caption'];
				$result[1][] = $item['id'];
				$sub = $this->menuBranch($item['id'], $level+1);
				if (count($sub[0]))
				{
					$result[0] = array_merge($result[0], $sub[0]);
					$result[1] = array_merge($result[1], $sub[1]);
				}
			}
		}
		return $result;
	}
	//-----------------------------------------------------------------------------

	/**
	 * Добавляет баннер в БД
	 *
	 * @return void
	 */
	private function insert()
	{
		global $Eresus, $request;

		$item = array();
		$item['caption'] = arg('caption', 'dbsafe');
		$item['active'] = arg('active', 'int');
		if (arg('section'))
		{
			$item['section'] = '|'.implode('|', arg('section')).'|';
		}
		$item['block'] = arg('block', 'dbsafe');
		$item['priority'] = arg('priority', 'int');
		$item['showFrom'] = arg('showFrom', 'dbsafe');
		if (arg('showTill'))
		{
			$item['showTill'] = arg('showTill', 'dbsafe');
		}
		$item['showCount'] = arg('showCount', 'int');
		$item['html'] = arg('html', 'dbsafe');
		$item['image'] = arg('image');
		$item['width'] = arg('width', 'int');
		$item['height'] = arg('height', 'int');
		$item['url'] = arg('url', 'dbsafe');
		$item['target'] = arg('target', 'dbsafe');

		$Eresus->db->insert($this->table['name'], $item);

		$item['id'] = $Eresus->db->getInsertedID();
		if (is_uploaded_file($_FILES['image']['tmp_name']))
		{
			$filename = 'banner'.$item['id'].substr($_FILES['image']['name'],
				strrpos($_FILES['image']['name'], '.'));
			upload('image', filesRoot.'data/'.$this->name.'/'.$filename);
			$item['image'] = $filename;
			$Eresus->db->updateItem($this->table['name'], $item, "`id`='".$item['id']."'");
		}
		HTTP::redirect($request['arg']['submitURL']);
	}
	//-----------------------------------------------------------------------------

	/**
	 * Обновляет баннер в БД
	 * @return void
	 */
	private function update()
	{
		global $Eresus, $request;

		$item = $Eresus->db->selectItem($this->table['name'], "`id`='".$request['arg']['update']."'");
		$old_file = $item['image'];
		$item['caption'] = arg('caption', 'dbsafe');
		$item['active'] = arg('active', 'int');
		if (arg('section'))
		{
			$item['section'] = '|'.implode('|', arg('section')).'|';
		}
		$item['block'] = arg('block', 'dbsafe');
		$item['priority'] = arg('priority', 'int');
		$item['showFrom'] = arg('showFrom', 'dbsafe');
		$item['showTill'] = arg('showTill', 'dbsafe');
		$item['showCount'] = arg('showCount', 'int');
		$item['html'] = arg('html', 'dbsafe');
		$item['image'] = arg('image');
		$item['width'] = arg('width', 'int');
		$item['height'] = arg('height', 'int');
		$item['url'] = arg('url', 'dbsafe');
		$item['target'] = arg('target', 'dbsafe');

		if ($item['showTill'] == '')
		{
			unset($item['showTill']);
		}
		if (arg('flushShowCount'))
		{
			$item['shows'] = 0;
		}
		if (is_uploaded_file($_FILES['image']['tmp_name']))
		{
			$path = filesRoot.'data/'.$this->name.'/';
			if (is_file($path.$old_file))
			{
				unlink($path.$old_file);
			}
			$filename = 'banner' . $item['id'] .
				substr($_FILES['image']['name'], strrpos($_FILES['image']['name'], '.'));
			upload('image', $path.$filename);
			$item['image'] = $filename;
		}

		$Eresus->db->updateItem($this->table['name'], $item, "`id`='".$item['id']."'");

		if ($item['showTill'] == '')
		{
			$Eresus->db->query(
				"UPDATE ".$Eresus->db->options->tableNamePrefix.$this->table['name'].
				" SET `showTill` = NULL WHERE `id`='".$item['id']."'");
		}

		HTTP::redirect($request['arg']['submitURL']);
	}
	//-----------------------------------------------------------------------------

	/**
	 * Переключает активность баннера
	 *
	 * @param int $id
	 * @return void
	 */
	private function toggle($id)
	{
		global $Eresus, $page;

		$item = $Eresus->db->selectItem($this->table['name'], "`id`='".$id."'");
		$item['active'] = !$item['active'];

		$item = $Eresus->db->escape($item);
		$Eresus->db->updateItem($this->table['name'], $item, "`id`='".$id."'");

		HTTP::redirect($page->url());
	}
	//-----------------------------------------------------------------------------

	/**
	 * Удаляет баннер
	 *
	 * @param int $id
	 * @return void
	 */
	private function delete($id)
	{
		global $page, $Eresus;

		$item = $Eresus->db->selectItem($this->table['name'], "`id`='".$id."'");
		$path = dataFiles.$this->name.'/';
		if (
			!empty($item['image']) &&
			file_exists($path.$item['image'])
		)
		{
			unlink($path.$item['image']);
		}
		$item = $Eresus->db->selectItem($this->table['name'], "`".$this->table['key']."`='".$id."'");
		$Eresus->db->delete($this->table['name'], "`".$this->table['key']."`='".$id."'");
		HTTP::redirect(str_replace('&amp;', '&', $page->url()));
	}
	//-----------------------------------------------------------------------------

	/**
	 * Возвращает диалог добавления баннера
	 *
	 * @return string  HTML
	 */
	function create()
	{
		global $page;

		$sections = array(array(), array());
		$sections = $this->menuBranch();
		array_unshift($sections[0], 'ВСЕ РАЗДЕЛЫ');
		array_unshift($sections[1], 'all');
		$form = array(
			'name' => 'formCreate',
			'caption' => 'Добавить баннер',
			'width' => '600px',
			'fields' => array (
				array ('type'=>'hidden','name'=>'action', 'value'=>'insert'),
				array ('type' => 'edit', 'name' => 'caption', 'label' => '<b>Заголовок</b>',
					'width' => '100%', 'maxlength' => '255', 'pattern'=>'/.+/',
					'errormsg'=>'Заголовок не может быть пустым!'),
				array ('type' => 'listbox', 'name' => 'section', 'label' => '<b>Разделы</b>', 'height'=> 5,
					'items'=>$sections[0], 'values'=>$sections[1]),
				array ('type' => 'edit', 'name' => 'block', 'label' => '<b>Блок баннера</b>',
					'width' => '100px', 'maxlength' => 31,
					'comment' => 'Для вставки баннера используйте макрос <b>$(Banners:имя_блока)</b>',
					'pattern'=>'/.+/', 'errormsg'=>'Не указан блок баннера!'),
				array ('type' => 'edit', 'name' => 'priority', 'label' => 'Приоритет', 'width' => '20px',
					'comment' => 'Если для одного раздела и одного блока задано несколько баннеров, будет ' .
					'показан с большим приоритетом',
					'default'=>0, 'pattern'=>'/\d+/', 'errormsg'=>'Приоритет задается только цифрами!'),
				array ('type' => 'edit', 'name' => 'showFrom', 'label' => 'Начало показов',
					'width' => '100px', 'comment' => 'ГГГГ-ММ-ДД', 'default'=>gettime('Y-m-d'),
					'pattern'=>'/[12]\d{3,3}-[01]\d-[0-3]\d/', 'errormsg'=>'Неправильный формат даты!'),
				array ('type' => 'edit', 'name' => 'showTill', 'label' => 'Конец показов',
					'width' => '100px', 'comment' => 'ГГГГ-ММ-ДД; Пустое - без ограничений',
					'pattern'=>'/([12]\d{3,3}-[01]\d-[0-3]\d)|(^$)/',
					'errormsg'=>'Неправильный формат даты!'),
				array ('type' => 'edit', 'name' => 'showCount', 'label' => 'Макс. кол-во показов',
					'width' => '100px', 'comment' => '0 - без ограничений', 'default'=>0,
					'pattern'=>'/(\d+)|(^$)/', 'errormsg'=>'Кол-во показов задается только цифрами!'),
				/*array ('type' => 'edit', 'name' => 'mail', 'label' => 'e-mail владельца',
					'width' => '200px', 'maxlength' => '63'),*/
				array ('type' => 'checkbox', 'name' => 'active', 'label' => 'Активировать',
					'default' => true),
				array ('type' => 'header', 'value' => 'Свойства баннера'),
				array ('type' => 'file', 'name' => 'image', 'label' => 'Картинка или Flash', 'width'=>'50'),
				array ('type' => 'edit', 'name' => 'width', 'label' => 'Ширина', 'width' => '100px',
					'comment'=>'только для Flash'),
				array ('type' => 'edit', 'name' => 'height', 'label' => 'Высота', 'width' => '100px',
					'comment'=>'только для Flash'),
				array ('type' => 'edit', 'name' => 'url', 'label' => 'URL для ссылки', 'width' => '100%',
					'maxlength' => '255'),
				array ('type' => 'select', 'name' => 'target', 'label' => 'Открывать',
					'items'=>array('в новом окне', 'в том же окне')),
				array ('type' => 'header', 'value' => 'HTML-код баннера'),
				array ('type' => 'memo', 'name' => 'html',
					'label' => 'HTML-код (Если задан HTML-код, то предыдущие свойства игнорируются и могут ' .
					'не заполняться)',
					'height' => '4'),
			),
			'buttons' => array('ok', 'cancel'),
		);

		$result = $page->renderForm($form);
		return $result;
	}
	//-----------------------------------------------------------------------------

	/**
	 * Возвращает диалог изменения баннера
	 *
	 * @return string  HMTL
	 */
	function edit()
	{
		global $page, $Eresus, $request;

		$item = $Eresus->db->selectItem($this->table['name'], "`id`='".$request['arg']['id']."'");
		$item['section'] = explode('|', $item['section']);
		$sections = array(array(), array());
		$sections = $this->menuBranch();
		array_unshift($sections[0], 'ВСЕ РАЗДЕЛЫ');
		array_unshift($sections[1], 'all');
		$form = array(
			'name' => 'formEdit',
			'caption' => 'Изменить баннер',
			'width' => '95%',
			'fields' => array (
				array ('type' => 'hidden','name'=>'update', 'value'=>$item['id']),
				array ('type' => 'edit', 'name' => 'caption', 'label' => '<b>Заголовок</b>',
					'width' => '100%', 'maxlength' => '255', 'pattern'=>'/.+/',
					'errormsg'=>'Заголовок не может быть пустым!'),
				array ('type' => 'listbox', 'name' => 'section', 'label' => '<b>Разделы</b>', 'height'=> 5,
					'items'=>$sections[0], 'values'=>$sections[1]),
				array ('type' => 'edit', 'name' => 'block', 'label' => '<b>Блок баннера</b>',
					'width' => '100px', 'maxlength' => 15,
					'comment' => 'Для вставки баннера используйте макрос <b>$(Banners:имя_блока)</b>',
					'pattern'=>'/.+/', 'errormsg'=>'Не указан блок баннера!'),
				array ('type' => 'edit', 'name' => 'priority', 'label' => 'Приоритет', 'width' => '20px',
					'comment' => 'Если для одного раздела и одного блока задано несколько баннеров, будет показан с большим приоритетом', 'default'=>0, 'pattern'=>'/\d+/', 'errormsg'=>'Приоритет задается только цифрами!'),
				array ('type' => 'edit', 'name' => 'showFrom', 'label' => 'Начало показов',
					'width' => '100px', 'comment' => 'ГГГГ-ММ-ДД', 'default'=>gettime('Y-m-d'),
					'pattern'=>'/[12]\d{3,3}-[01]\d-[0-3]\d/', 'errormsg'=>'Неправильный формат даты!'),
				array ('type' => 'edit', 'name' => 'showTill', 'label' => 'Конец показов',
					'width' => '100px', 'comment' => 'ГГГГ-ММ-ДД; Пустое - без ограничений',
					'pattern'=>'/(\d{4,4}-[01]\d-[0-3]\d)|(^$)/', 'errormsg'=>'Неправильный формат даты!'),
				array ('type' => 'edit', 'name' => 'showCount', 'label' => 'Макс. кол-во показов',
					'width' => '100px', 'comment' => '0 - без ограничений', 'default'=>0,
						'pattern'=>'/(\d+)|(^$)/', 'errormsg'=>'Кол-во показов задается только цифрами!'),
				//array ('type' => 'edit', 'name' => 'mail', 'label' => 'e-mail владельца',
				//'width' => '200px', 'maxlength' => '63'),
				array ('type' => 'checkbox', 'name' => 'active', 'label' => 'Активировать'),
				array ('type' => 'header', 'value' => 'Свойства баннера'),
				array ('type' => 'file', 'name' => 'image', 'label' => 'Картинка или Flash', 'width'=>'50',
					'comment' => '<a></a>'),
				array ('type' => 'edit', 'name' => 'width', 'label' => 'Ширина', 'width' => '100px',
					'comment'=>'только для Flash'),
				array ('type' => 'edit', 'name' => 'height', 'label' => 'Высота', 'width' => '100px',
					'comment'=>'только для Flash'),
				array ('type' => 'edit', 'name' => 'url', 'label' => 'URL для ссылки', 'width' => '100%',
					'maxlength' => '255'),
				array ('type' => 'select', 'name' => 'target', 'label' => 'Открывать',
					'items'=>array('в новом окне', 'в том же окне')),
				array ('type' => 'header', 'value' => 'HTML-код баннера'),
				array ('type' => 'memo', 'name' => 'html',
					'label' => 'HTML-код (Если задан HTML-код, то предыдущие свойства игнорируются и могут' .
					'не заполняться)',
					'height' => '4'),
				array ('type' => 'divider'),
				array ('type' => 'checkbox', 'name' => 'flushShowCount',
					'label' => 'Обнулить кол-во показов'),
			),
			'buttons' => array('ok', 'apply', 'cancel'),
		);

		$result = $page->renderForm($form, $item);
		return $result;
	}
	//-----------------------------------------------------------------------------

	/**
	 * Возвращает разметку списка баннеров
	 *
	 * @return string  HTML
	 */
	function adminRender()
	{
		global $Eresus, $page, $request;

		$result = '';
		if (isset($request['arg']['id']))
		{
			$item = $Eresus->db->selectItem($this->table['name'],
				"`".$this->table['key']."` = '".$request['arg']['id']."'");
			$page->title .= empty($item['caption'])?'':' - '.$item['caption'];
		}
		if (isset($request['arg']['update']) && isset($this->table['controls']['edit']))
		{
			$result = $this->update();
		}
		elseif (isset($request['arg']['toggle']) && isset($this->table['controls']['toggle']))
		{
			$result = $this->toggle($request['arg']['toggle']);
		}
		elseif (isset($request['arg']['delete']) && isset($this->table['controls']['delete']))
		{
			$result = $this->delete($request['arg']['delete']);
		}
		elseif (isset($request['arg']['id']) && isset($this->table['controls']['edit']))
		{
			$result = $this->edit();
		}
		elseif (isset($request['arg']['action'])) switch ($request['arg']['action'])
		{
			case 'create':
				$result = $this->create();
			break;
			case 'insert':
				$result = $this->insert();
			break;
		}
		else
		{
			$result = $page->renderTable($this->table);
		}
		return $result;
	}
	//-----------------------------------------------------------------------------


	function adminRenderContent()
	{
		global $Eresus, $page;

		$result = '';
		if (!is_null(arg('id')))
		{
			$item = $Eresus->db->selectItem($this->table['name'],
				"`".$this->table['key']."` = '".arg('id', 'dbsafe')."'");
			$page->title .= empty($item['caption'])?'':' - '.$item['caption'];
		}
		switch (true)
		{
			case !is_null(arg('update')):
				$result = $this->update();
			break;
			case !is_null(arg('toggle')):
				$result = $this->toggle(arg('toggle', 'dbsafe'));
			break;
			case !is_null(arg('delete')):
				$result = $this->delete(arg('delete', 'dbsafe'));
			break;
			case !is_null(arg('id')):
				$result = $this->adminEditItem();
			break;
			case !is_null(arg('action')):
				switch (arg('action'))
				{
					case 'create':
						$result = $this->adminAddItem();
					break;
					case 'insert':
						$result = $this->insert();
					break;
				}
			break;
			default:
				if (!is_null(arg('section')))
				{
					$this->table['condition'] = "`section`='".arg('section', 'int')."'";
				}
				$result = $page->renderTable($this->table);
		}
		return $result;
	}
	//-----------------------------------------------------------------------------

	/**
	 *
	 * @return unknown_type
	 */
	function adminOnMenuRender()
	{
		global $page;

		$page->addMenuItem(admExtensions, array('access' => EDITOR, 'link' => $this->name,
			'caption'	=> $this->title, 'hint'	=> $this->description));
	}
	//-----------------------------------------------------------------------------

	/**
	 * Отрисовка баннеров и обработка кликов
	 *
	 * @param string $text  HTML страницы
	 * @return string  HTML страницы
	 */
	public function clientOnPageRender($text)
	{
		global $Eresus, $page;

		if (arg('banners-click'))
		{
			/*
			 * Если передан аргумент banners-click, надо перенаправить польщователя на URL баннера
			 */
			if (count($Eresus->request['arg']) != 1)
			{
				$page->httpError(404);
			}

			$id = arg('banners-click', 'int');
			if ($id == '' | $id != arg('banners-click'))
			{
				$page->httpError(404);
			}

			$this->processClick($id);

		}
		else
		{
			// Ищем все места встаки баннеров
			preg_match_all('/\$\(Banners:([^)]+)\)/', $text, $blocks,
				PREG_SET_ORDER | PREG_OFFSET_CAPTURE);
			$delta = 0;
			foreach ($blocks as $block)
			{
				$sql = "(`active`=1) AND (`section` LIKE '%|" . $page->id .
					"|%' OR `section` LIKE '%|all|%') AND (`block`='" . $block[1][0 ] .
					"') AND (`showFrom`<='" . gettime() .
					"') AND (`showCount`=0 OR (`shows` < `showCount`)) AND " .
					"(`showTill` = '0000-00-00' OR `showTill` IS NULL OR `showTill` > '" .
					gettime() . "')";

				// Получаем баннеры для этого блока в порядке уменьшения приоритета
				$items = $this->dbSelect('', $sql, '-priority');
				if (count($items))
				{
					/* Отсекаем баннеры с низким приоритетом */
					$priority = $items[0]['priority'];
					for ($i = 0; $i < count($items); $i++)
					{
						if ($items[$i]['priority'] != $priority)
						{
							$items = array_slice($items, 0, $i);
							break;
						}
					}

					// Выбираем случайный баннер
					$item = $items[mt_rand(0, count($items)-1)];
					$item['shows']++;
					$banner = BannersFactory::createFromArray($item);

					$Eresus->db->updateItem($this->name, $Eresus->db->escape($item),
						"`id`='".$item['id']."'");

					$code = $banner->render();
					$text = substr_replace($text, $code, $block[0][1]+$delta, strlen($block[0][0]));
					$delta += strlen($code) - strlen($block[0][0]);
				}
			}
			$items = $Eresus->db->select($this->table['name'],
				"(`showCount` != 0 AND `shows` > `showCount`) AND ((`showTill` < '" . gettime() .
				"') AND (`showTill` != '0000-00-00'))");
			if (count($items))
			{
				foreach ($items as $item)
				{
					/* sendMail($item['mail'], 'Ваш баннер деактивирован', 'Ваш баннер "'.$item['caption'].
					 ' был отключен, т.к. так как превышены количество показов либо дата показа."'); */
					sendMail(option('sendNotifyTo'), 'Баннер деактивирован', 'Баннер "' .
						$item['caption'] . ' был отключен системой управления сайтом."');
				}
				$Eresus->db->update($this->table['name'],
					"`active`='0'", "(`showCount` != 0 AND `shows` > `showCount`) AND ((`showTill` < '" .
					gettime() . "') AND (`showTill` != '0000-00-00'))");
			}
		}
		return $text;
	}
	//-----------------------------------------------------------------------------

	/**
	 * Перенаправляет посетителя на URL, заданный баннером
	 *
	 * @param int $id  Идентификатор баннера
	 */
	private function processClick($id)
	{
		$item = $this->dbItem('', $id);
		if ($item)
		{
			$item['clicks']++;
			$this->dbUpdate('', $item);

			$url = $GLOBALS['page']->replaceMacros($item['url']);

			if ($url == '#')
			{
				HTTP::goback();
			}
			else
			{
				HTTP::redirect($url);
			}
		}
		else
		{
			$GLOBALS['page']->httpError(404);
		}
	}
	//-----------------------------------------------------------------------------

	protected function createTable($table)
	{
		global $Eresus;

		$Eresus->db->query('CREATE TABLE IF NOT EXISTS `' . $Eresus->db->prefix . $table['name'] .
			'`'.$table['sql']);
	}
	//-----------------------------------------------------------------------------
}



/**
 * Баннер
 *
 * @package Banners
 */
abstract class AbstractBanner
{
	/**
	 * Свойства баннера
	 *
	 * @var array
	 */
	protected $data;

	/**
	 * Конструктор баннера
	 *
	 * @param array $data
	 * @return AbstractBanner
	 */
	public function __construct($data)
	{
		$this->data = $data;
	}
	//-----------------------------------------------------------------------------

	/**
	 * Метод должен возвращать разметку баннера для добавления на страницу
	 *
	 * @return string  HTML
	 */
	abstract public function render();
	//-----------------------------------------------------------------------------

	/**
	 * Возвращает объект плагина Banners
	 *
	 * @return Banners
	 */
	protected function getPlugin()
	{
		$plugin = $GLOBALS['Eresus']->plugins->load('banners');
		return $plugin;
	}
	//-----------------------------------------------------------------------------
}



/**
 * Текстовый баннер
 *
 * @package Banners
 */
class TextBanner extends AbstractBanner
{
	/**
	 * Возвращает кода баннера для вставки на страницу
	 *
	 * @return string  HTML
	 * @see AbstractBanner::render()
	 */
	public function render()
	{
		return $this->data['html'];
	}
	//-----------------------------------------------------------------------------
}



/**
 * Графический баннер
 *
 * @package Banners
 */
class ImageBanner extends AbstractBanner
{
	/**
	 * Возвращает кода баннера для вставки на страницу
	 *
	 * @return string  HTML
	 * @see AbstractBanner::render()
	 */
	public function render()
	{
		global $Eresus;

		$plugin = $this->getPlugin();

		$html = img($plugin->getDataDir() . $this->data['image']);

		if (!empty($this->data['url']))
		{
			$template = '<a href="%s"%s>%s</a>';

			$url = $Eresus->request['path'] . '?banners-click=' .	$this->data['id'];
			$target = $this->data['target'] ? '' : ' target="_blank"';

			$html = sprintf($template, $url, $target, $html);
		}

		return $html;
	}
	//-----------------------------------------------------------------------------
}



/**
 * Flash-баннер
 *
 * @package Banners
 */
class FlashBanner extends AbstractBanner
{
	/**
	 * Возвращает кода баннера для вставки на страницу
	 *
	 * @return string  HTML
	 * @see AbstractBanner::render()
	 */
	public function render()
	{
		global $Eresus, $page;

		$plugin = $this->getPlugin();

		$template =
			'<object type="application/x-shockwave-flash" data="%s" width="%d" height="%d">' .
				'<param name="movie" value="%1$s" />' .
				'<param name="quality" value="high" />' .
				'<param name="wmode" value="opaque" />' .
			'</object>';

		$swf = $plugin->getDataURL() . $this->data['image'];
		$width = $this->data['width'];
		$height = $this->data['height'];

		$html = sprintf($template, $swf, $width, $height);

		if (!empty($this->data['url']))
		{
			$page->linkStyles($plugin->getCodeURL() . 'main.css');

			$template =
				'<div class="banners-swf-container">' .
					'<div class="banners-swf-overlay">' .
						'<a href="%1$s"%2$s><img src="%4$s" alt="" width="%5$d" height="%6$d" /></a>' .
					'</div>' .
					'%3$s' .
				'</div>';

			$url = $Eresus->request['path'] . '?banners-click=' .	$this->data['id'];
			$target = $this->data['target'] ? '' : ' target="_blank"';
			$stubImage = $Eresus->root . 'style/dot.gif';

			$html = sprintf($template, $url, $target, $html, $stubImage, $width, $height);
		}

		return $html;
	}
	//-----------------------------------------------------------------------------
}



/**
 * Фабрика баннеров
 *
 * Класс предназначен для создания объектов баннеров
 *
 * @package Banners
 */
class BannersFactory
{
	/**
	 * Создаёт объект баннера из массива его свойств
	 *
	 * @param array $data
	 * @return Banner  Объект баннера
	 */
	public static function createFromArray($data)
	{
		switch (true)
		{
			case $data['html'] != '':
				return new TextBanner($data);

			case preg_match('/\.swf$/i', $data['image']):
				return new FlashBanner($data);

			default:
				return new ImageBanner($data);
		}
	}
	//-----------------------------------------------------------------------------
}

