<?
#-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-#
# CMS Eresus™ 2.00+
# © 2005-2006, ProCreat Systems
# Web: http://procreat.ru
#-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-#
class TBanners extends TListContentPlugin {
  var $name = 'banners';
  var $title = 'Баннеры';
  var $type = 'client,admin';
  var $version = '1.01';
  var $description = 'Система показа баннеров';
  var $table = array (
    'name' => 'banners',
    'key'=> 'id',
    'sortMode' => 'id',
    'sortDesc' => false,
    'columns' => array(
      array('name' => 'caption', 'caption' => 'Название'),
      array('name' => 'block', 'caption' => 'Блок', 'align'=> 'right'),
      array('name' => 'priority', 'caption' => '<span title="Приоритет" style="cursor: default;">&nbsp;&nbsp;*</span>', 'align'=>'center'),
      array('name' => 'showTill', 'caption' => 'До даты', 'replace'=> array('0000-00-00'=> 'без огранич.')),
      array('name' => 'showCount', 'caption' => 'Макс.показ.', 'align'=>'right', 'replace' => array('0'=> 'без огранич.')),
      array('name' => 'shows', 'caption' => 'Показан', 'align'=>'right'),
      array('name' => 'clicks', 'caption' => 'Кликов', 'align'=>'right'),
      array('name' => 'mail', 'caption' => 'Владелец', 'value' => '<a href="mailto:$(mail)">$(mail)</a>', 'macros' => true),
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
      `block` int(10) unsigned default NULL,
      `showFrom` date default NULL,
      `showTill` date default NULL,
      `showCount` int(10) unsigned default NULL,
      `html` text,
      `image` varchar(255) default NULL,
      `url` varchar(255) default NULL,
      `target` tinyint(1) unsigned default NULL,
      `shows` bigint(20) unsigned default NULL,
      `clicks` bigint(20) unsigned default NULL,
      PRIMARY KEY  (`id`),
      KEY `active` (`active`),
      KEY `priority` (`priority`),
      KEY `showFrom` (`showFrom`),
      KEY `showTill` (`showTill`),
      KEY `showCount` (`showCount`),
      KEY `shows` (`shows`)
    ) TYPE=MyISAM COMMENT='Banner system';",
  );
  #--------------------------------------------------------------------------------------------------------------------------------------------------------------# 
  function install()
  {
    parent::install();
    umask(0000);
    if (!file_exists(filesRoot.'data/'.$this->name)) mkdir(filesRoot.'data/'.$this->name, 0777);
  }
  #--------------------------------------------------------------------------------------------------------------------------------------------------------------#
  # Стандартные функции
  #--------------------------------------------------------------------------------------------------------------------------------------------------------------# 
  function TBanners()
  # производит регистрацию обработчиков событий
  {
  global $plugins;
  
    parent::TPlugin();
    if (defined('CLIENTUI')) $plugins->events['clientOnPageRender'][] = $this->name; 
    else $plugins->events['adminOnMenuRender'][] = $this->name;
  }
  #--------------------------------------------------------------------------------------------------------------------------------------------------------------# 
  # Внутренние функции
  #--------------------------------------------------------------------------------------------------------------------------------------------------------------# 
  function menuBrunch($owner = 0, $level = 0)
  {
  global $db;
    $result = array(array(), array());
    $items = $db->select('`pages`', "(`access`>='".USER."')AND(`owner`='".$owner."') AND (`active`='1')", "`position`", false, "`id`,`caption`");
    if (count($items)) foreach($items as $item) {
      $result[0][] = str_repeat('- ', $level).$item['caption'];
      $result[1][] = $item['id'];
      $sub = $this->menuBrunch($item['id'], $level+1);
      if (count($sub[0])) {
        $result[0] = array_merge($result[0], $sub[0]);
        $result[1] = array_merge($result[1], $sub[1]);
      }
    }
    return $result;
  }
  #--------------------------------------------------------------------------------------------------------------------------------------------------------------# 
  function insert()
  {
  global $db, $request;

    $item = getArgs($db->fields($this->table['name']));
    $item['section'] = ($item['section'] != 'all')?':'.implode(':', $request['arg']['section']).':':'all';
    $db->insert($this->table['name'], $item);
    $item['id'] = $db->getInsertedID();
    if (is_uploaded_file($_FILES['image']['tmp_name'])) {
      $filename = 'banner'.$item['id'].substr($_FILES['image']['name'], strrpos($_FILES['image']['name'], '.'));
      upload('image', filesRoot.'data/'.$this->name.'/'.$filename);
      $item['image'] = $filename;
      $db->updateItem($this->table['name'], $item, "`id`='".$item['id']."'");
    }
    sendNotify('Добавлен баннер: '.$item['caption']);
    goto($request['arg']['submitURL']);
  }
  #--------------------------------------------------------------------------------------------------------------------------------------------------------------#
  function update()
  {
  global $db, $request;

    $item = $db->selectItem($this->table['name'], "`id`='".$request['arg']['update']."'");
    $old_file = $item['image'];
    $item = setArgs($item);
    $item['section'] = ($item['section'] != 'all')?':'.implode(':', $request['arg']['section']).':':'all';
    if (!isset($request['arg']['active'])) $item['active'] = false;
    if (is_uploaded_file($_FILES['image']['tmp_name'])) {
      $path = filesRoot.'data/'.$this->name.'/';
      if (file_exists($path.$old_file)) unlink($path.$old_file);
      $filename = 'banner'.$item['id'].substr($_FILES['image']['name'], strrpos($_FILES['image']['name'], '.'));
      upload('image', $path.$filename);
      $item['image'] = $filename;
    }
    $db->updateItem($this->table['name'], $item, "`id`='".$request['arg']['update']."'");
    sendNotify('Изменен баннер: '.$item['caption']);
    goto($request['arg']['submitURL']);
  }
  #--------------------------------------------------------------------------------------------------------------------------------------------------------------#
  function toggle($id)
  {
  global $db, $page, $request;
  
    $item = $db->selectItem($this->table['name'], "`id`='".$id."'");
    $item['active'] = !$item['active'];
    $db->updateItem($this->table['name'], $item, "`id`='".$id."'");
    sendNotify(($item['active']?admActivated:admDeactivated).': '.'<a href="'.str_replace('toggle','id',$request['url']).'">'.$item['caption'].'</a>', array('title'=>$this->title));
    goto($page->url());
  }
  #--------------------------------------------------------------------------------------------------------------------------------------------------------------#
  function delete($id)
  {
  global $db, $page, $request;
  
    $item = $db->selectItem($this->table['name'], "`id`='".$id."'");
    $path = dataFiles.$this->name.'/';
    if (!empty($item['image']) && file_exists($path.$item['image'])) unlink($path.$item['image']);
    #$item['active'] = !$item['active'];
    #$db->updateItem($this->table['name'], $item, "`id`='".$id."'");
    #sendNotify(($item['active']?admActivated:admDeactivated).': '.'<a href="'.str_replace('toggle','id',$request['url']).'">'.$item['caption'].'</a>', array('title'=>$this->title));
    parent::delete($id);
    #goto($page->url());
  }
  #--------------------------------------------------------------------------------------------------------------------------------------------------------------#
  # Административные функции
  #--------------------------------------------------------------------------------------------------------------------------------------------------------------# 
  function create()
  {
  global $page, $db;

    $sections = array(array(), array());
    $sections = $this->menuBrunch();
    array_unshift($sections[0], 'ВСЕ РАЗДЕЛЫ');
    array_unshift($sections[1], 'all');
    $form = array(
      'name' => 'formCreate',
      'caption' => 'Добавить баннер',
      'width' => '95%',
      'fields' => array (
        array ('type'=>'hidden','name'=>'action', 'value'=>'insert'),
        array ('type' => 'edit', 'name' => 'caption', 'label' => 'Заголовок', 'width' => '100%', 'maxlength' => '255', 'pattern'=>'/.+/', 'errormsg'=>'Заголовок не может быть пустым!'),
        array ('type' => 'listbox', 'name' => 'section[]', 'label' => 'Разделы', 'height'=> 5,'items'=>$sections[0], 'values'=>$sections[1]),
        array ('type' => 'edit', 'name' => 'priority', 'label' => 'Приоритет', 'width' => '20px', 'comment' => 'Большие значения - больший приоритет', 'value'=>0, 'pattern'=>'/\d+/', 'errormsg'=>'Приоритет задается только цифрами!'),
        array ('type' => 'edit', 'name' => 'block', 'label' => 'Номер блока', 'width' => '20px', 'pattern'=>'/(\d+)|(^$)/', 'errormsg'=>'Номер блока задается только цифрами!'),
        array ('type' => 'edit', 'name' => 'showFrom', 'label' => 'Начало показов', 'width' => '100px', 'comment' => 'ГГГГ-ММ-ДД', 'value'=>gettime('Y-m-d'), 'pattern'=>'/[12]\d{3,3}-[01]\d-[0-3]\d/', 'errormsg'=>'Неправильный формат даты!'),
        array ('type' => 'edit', 'name' => 'showTill', 'label' => 'Конец показов', 'width' => '100px', 'comment' => 'ГГГГ-ММ-ДД; Пустое - без ограничений', 'pattern'=>'/([12]\d{3,3}-[01]\d-[0-3]\d)|(^$)/', 'errormsg'=>'Неправильный формат даты!'),
        array ('type' => 'edit', 'name' => 'showCount', 'label' => 'Макс. кол-во показов', 'width' => '100px', 'comment' => '0 - без ограничений', 'value'=>0, 'pattern'=>'/(\d+)|(^$)/', 'errormsg'=>'Кол-во показов задается только цифрами!'),
        array ('type' => 'memo', 'name' => 'html', 'label' => 'HTML-код', 'height' => '4'),
        array ('type' => 'file', 'name' => 'image', 'label' => 'Картинка', 'width'=>'50'),
        array ('type' => 'edit', 'name' => 'url', 'label' => 'URL для ссылки', 'width' => '100%', 'maxlength' => '255'),
        array ('type' => 'select', 'name' => 'target', 'label' => 'Открывать', 'items'=>array('в новом окне', 'в том же окне')),
        array ('type' => 'checkbox', 'name' => 'active', 'label' => 'Активировать', 'value'=>true),
        array ('type' => 'edit', 'name' => 'mail', 'label' => 'e-mail владельца', 'width' => '200px', 'maxlength' => '63'),
      ),
      'buttons' => array('ok', 'cancel'),
    );

    $result = $page->renderForm($form);
    return $result;
  }
  #--------------------------------------------------------------------------------------------------------------------------------------------------------------#
  function edit()
  {
  global $page, $db, $request;

    $item = $db->selectItem($this->table['name'], "`id`='".$request['arg']['id']."'");
    $item['section[]'] = explode(':', $item['section']);
    $sections = array(array(), array());
    $sections = $this->menuBrunch();
    array_unshift($sections[0], 'ВСЕ РАЗДЕЛЫ');
    array_unshift($sections[1], 'all');
    $form = array(
      'name' => 'formEdit',
      'caption' => 'Изменить баннер',
      'width' => '95%',
      'fields' => array (
        array ('type' => 'hidden','name'=>'update', 'value'=>$item['id']),
        array ('type' => 'edit', 'name' => 'caption', 'label' => 'Заголовок', 'width' => '100%', 'maxlength' => '255', 'pattern'=>'/.+/', 'errormsg'=>'Заголовок не может быть пустым!'),
        array ('type' => 'listbox', 'name' => 'section[]', 'label' => 'Разделы', 'height'=> 5,'items'=>$sections[0], 'values'=>$sections[1]),
        array ('type' => 'edit', 'name' => 'priority', 'label' => 'Приоритет', 'width' => '20px', 'comment' => 'Большие значения - больший приоритет', 'value'=>0, 'pattern'=>'/\d+/', 'errormsg'=>'Приоритет задается только цифрами!'),
        array ('type' => 'edit', 'name' => 'block', 'label' => 'Номер блока', 'width' => '20px', 'pattern'=>'/(\d+)|(^$)/', 'errormsg'=>'Номер блока задается только цифрами!'),
        array ('type' => 'edit', 'name' => 'showFrom', 'label' => 'Начало показов', 'width' => '100px', 'comment' => 'ГГГГ-ММ-ДД', 'value'=>gettime('Y-m-d'), 'pattern'=>'/[12]\d{3,3}-[01]\d-[0-3]\d/', 'errormsg'=>'Неправильный формат даты!'),
        array ('type' => 'edit', 'name' => 'showTill', 'label' => 'Конец показов', 'width' => '100px', 'comment' => 'ГГГГ-ММ-ДД; Пустое - без ограничений', 'pattern'=>'/(\d{4,4}-[01]\d-[0-3]\d)|(^$)/', 'errormsg'=>'Неправильный формат даты!'),
        array ('type' => 'edit', 'name' => 'showCount', 'label' => 'Макс. кол-во показов', 'width' => '100px', 'comment' => '0 - без ограничений', 'value'=>0, 'pattern'=>'/(\d+)|(^$)/', 'errormsg'=>'Кол-во показов задается только цифрами!'),
        array ('type' => 'memo', 'name' => 'html', 'label' => 'HTML-код', 'height' => '4'),
        array ('type' => 'file', 'name' => 'image', 'label' => 'Картинка', 'width'=>'50'),
        array ('type' => 'edit', 'name' => 'url', 'label' => 'URL для ссылки', 'width' => '100%', 'maxlength' => '255'),
        array ('type' => 'select', 'name' => 'target', 'label' => 'Открывать', 'items'=>array('в новом окне', 'в том же окне')),
        array ('type' => 'checkbox', 'name' => 'active', 'label' => 'Активировать'),
        array ('type' => 'edit', 'name' => 'mail', 'label' => 'e-mail владельца', 'width' => '200px', 'maxlength' => '63'),
      ),
      'buttons' => array('ok', 'apply', 'cancel'),
    );

    $result = $page->renderForm($form, $item);
    return $result;
  }
  #--------------------------------------------------------------------------------------------------------------------------------------------------------------#
  function adminRender()
  {
  global $db, $page, $user, $request, $session;
  
    $result = '';
    if (isset($request['arg']['id'])) {
      $item = $db->selectItem($this->table['name'], "`".$this->table['key']."` = '".$request['arg']['id']."'");
      $page->title .= empty($item['caption'])?'':' - '.$item['caption'];
    }
    if (isset($request['arg']['update']) && isset($this->table['controls']['edit'])) {
      if (method_exists($this, 'update')) $result = $this->update(); else $session['errorMessage'] = sprintf(errMethodNotFound, 'update', get_class($this));
    } elseif (isset($request['arg']['toggle']) && isset($this->table['controls']['toggle'])) {
      if (method_exists($this, 'toggle')) $result = $this->toggle($request['arg']['toggle']); else $session['errorMessage'] = sprintf(errMethodNotFound, 'toggle', get_class($this));
    } elseif (isset($request['arg']['delete']) && isset($this->table['controls']['delete'])) {
      if (method_exists($this, 'delete')) $result = $this->delete($request['arg']['delete']); else $session['errorMessage'] = sprintf(errMethodNotFound, 'delete', get_class($this));
    } elseif (isset($request['arg']['id']) && isset($this->table['controls']['edit'])) {
      if (method_exists($this, 'edit')) $result = $this->edit(); else $session['errorMessage'] = sprintf(errMethodNotFound, 'edit', get_class($this));
    } elseif (isset($request['arg']['action'])) switch ($request['arg']['action']) {
      case 'create': $result = $this->create(); break;
      case 'insert':
        if (method_exists($this, 'insert')) $result = $this->insert(); 
        else $session['errorMessage'] = sprintf(errMethodNotFound, 'insert', get_class($this));
      break;
    } else {
      $result = $page->renderTable($this->table);
    }
    return $result;
  }
  #--------------------------------------------------------------------------------------------------------------------------------------------------------------# 
  /*function settings()
  {
  global $page;
  
    $form = array(
      'name'=>'SettingsForm',
      'caption' => $this->title.' '.$this->version,
      'width' => '500px',
      'fields' => array (
        array('type'=>'hidden','name'=>'update', 'value'=>$this->name),
        array('type'=>'select','name'=>'displayMode','label'=>'Режим', 'items'=>array('Заменять макрос $(plgBanners)', 'Использовать SideBars')),
        array('type'=>'edit','name'=>'caption','label'=>'Заголовок','width'=>'100px'),
        array('type'=>'select','name'=>'SideBarsPanel','label'=>'Колонка SideBars', 'values'=>array('left', 'right'), 'items'=>array('Левая', 'Правая')),
      ),
      'buttons' => array('ok', 'apply', 'cancel'),
    );
    $result = $page->renderForm($form, $this->settings);
    return $result;
  }*/
  #--------------------------------------------------------------------------------------------------------------------------------------------------------------# 
  # Обработчики событий
  #--------------------------------------------------------------------------------------------------------------------------------------------------------------# 
  function adminOnMenuRender()
  {
  global $page;
  
    $page->addMenuItem(admExtensions, array ('access'  => EDITOR, 'link'  => $this->name, 'caption'  => $this->title, 'hint'  => $this->description));
  }
  #--------------------------------------------------------------------------------------------------------------------------------------------------------------#
  function clientOnPageRender($text)
  {
  global $db, $page, $request;
  
    if (isset($request['arg']['click'])) {
      $item = $db->selectItem($this->name, "`id`='".$request['arg']['click']."'");
      $item['clicks']++;
      $db->updateItem($this->name, $item, "`id`='".$item['id']."'");
      goto($item['url']);
      exit;
    } else {
      preg_match_all('/\$\(plgBanners:(\d+?)\)/', $text, $blocks);
      foreach($blocks[1] as $block) {
        $sql = "(`active`=1) AND (`section` LIKE '%:".$page->id.":%' OR `section` = ':all:') AND (`block`=".$block.") AND (`showFrom`<='".gettime()."') AND (`showCount`=0 OR `shows` <= `showCount`) AND (`showTill` = '0000-00-00' OR `showTill` > '".gettime()."')";
        $item = $db->select($this->name, $sql, '`priority`', true);
        if (count($item)) {
          $item = $item[0];
          if (empty($item['html'])) {
            $banner = img(httpRoot.'data/'.$this->name.'/'.$item['image']);
            if (!empty($item['url'])) $banner = '<a href="'.httpRoot.execScript.'?click='.$item['id'].'"'.($item['target']?'':' target="_blank"').'>'.$banner.'</a>';
          } else {
            $banner = StripSlashes($item['html']);
          }
          $item['shows']++;
          $db->updateItem($this->name, $item, "`id`='".$item['id']."'");
          $text = str_replace('$(plgBanners:'.$block.')', $banner, $text);
        }
      }
      $text = preg_replace('/\$\(plgBanners:\d?\)/','', $text);
      $items = $db->select($this->table['name'], "(`showCount` != 0 AND `shows` > `showCount`) AND ((`showTill` < '".gettime()."') AND (`showTill` != '0000-00-00'))");
      if (count($items)) {
        foreach($items as $item) {
          sendMail($item['mail'], 'Ваш баннер деактивирован', 'Ваш баннер "'.$item['caption'].' был отключен, т.к. так как превышены количество показов либо дата показа."');
          sendMail(getOption('sendNotifyTo'), 'Баннер деактивирован', 'Баннер "'.$item['caption'].' был отключен системой управления сайтом."');
        }
        $db->update($this->table['name'], "`active`='0'", "(`showCount` != 0 AND `shows` > `showCount`) AND ((`showTill` < '".gettime()."') AND (`showTill` != '0000-00-00'))");
      }
    }
    return $text;
  }
  #--------------------------------------------------------------------------------------------------------------------------------------------------------------#
}
#-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-#
?>