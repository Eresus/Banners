<?php
/**
 * Система показа баннеров
 *
 * @version: ${product.version}
 *
 * @copyright 2005, Михаил Красильников <mihalych@vsepofigu.ru>
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
    public $kernel = '3.00a';

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
    public $version = '${product.version}';

    /**
     * Описание
     * @var string
     */
    public $description = 'Система показа баннеров';

    /**
     * Таблица АИ
     * @var array
     */
    private $table = array(
        'name' => 'banners',
        'key' => 'id',
        'sortMode' => 'id',
        'sortDesc' => false,
        'columns' => array(
            array('name' => 'caption', 'caption' => 'Название'),
            array('name' => 'block', 'caption' => 'Блок', 'align' => 'right'),
            array('name' => 'priority',
                'caption' => '<span title="Приоритет" style="cursor: default;">&nbsp;&nbsp;*</span>',
                'align' => 'center'),
            array('name' => 'showTill', 'caption' => 'До даты',
                'replace' => array('0000-00-00' => 'без огранич.')),
            array('name' => 'showCount', 'caption' => 'Макс.показ.', 'align' => 'right',
                'replace' => array('0' => 'без огранич.')),
            array('name' => 'shows', 'caption' => 'Показан', 'align' => 'right'),
            array('name' => 'clicks', 'caption' => 'Кликов', 'align' => 'right'),
            //array('name' => 'mail', 'caption' => 'Владелец',
            //'value' => '<a href="mailto:$(mail)">$(mail)</a>', 'macros' => true),
        ),
        'controls' => array(
            'delete' => '',
            'edit' => '',
            'toggle' => '',
        ),
        'tabs' => array(
            'width' => '180px',
            'items' => array(
                array('caption' => 'Добавить баннер', 'name' => 'action', 'value' => 'create')
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
		);",
    );

    /**
     * Конструктор
     *
     * Производит регистрацию обработчиков событий.
     */
    public function __construct()
    {
        parent::__construct();
        $this->listenEvents('clientOnPageRender', 'adminOnMenuRender');
    }

    /**
     * Возвращает путь к директории данных плагина
     *
     * @return string
     *
     * @since 2.00
     */
    public function getDataDir()
    {
        return $this->dirData;
    }

    /**
     * Действия при установке плагина
     */
    public function install()
    {
        parent::install();

        $this->createTable($this->table);

        $this->mkdir();
    }

    /**
     * Возвращает ветку разделов сайта
     *
     * @param int $owner
     * @param int $level
     *
     * @return array
     */
    private function menuBranch($owner = 0, $level = 0)
    {
        $result = array(array(), array());
        $items = Eresus_CMS::getLegacyKernel()->db->select('pages',
            "(`access` >= '" . USER . "') AND (`owner` = '" . $owner . "') AND (`active` = '1')",
            "-position", "`id`,`caption`");
        if (count($items))
        {
            foreach ($items as $item)
            {
                $result[0][] = str_repeat('- ', $level) . $item['caption'];
                $result[1][] = $item['id'];
                $sub = $this->menuBranch($item['id'], $level + 1);
                if (count($sub[0]))
                {
                    $result[0] = array_merge($result[0], $sub[0]);
                    $result[1] = array_merge($result[1], $sub[1]);
                }
            }
        }
        return $result;
    }

    /**
     * Добавляет баннер в БД
     *
     * @return void
     */
    private function insert()
    {
        $Eresus = Eresus_CMS::getLegacyKernel();

        $item = array();
        $item['caption'] = arg('caption', 'dbsafe');
        $item['active'] = arg('active', 'int');
        if (arg('section'))
        {
            $item['section'] = '|' . implode('|', arg('section')) . '|';
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
            $filename = 'banner' . $item['id'] . substr($_FILES['image']['name'],
                    strrpos($_FILES['image']['name'], '.'));
            upload('image', $Eresus->fdata . $this->name . '/' . $filename);
            $item['image'] = $filename;
            $Eresus->db->updateItem($this->table['name'], $item, "`id`='" . $item['id'] . "'");
        }
        HTTP::redirect(arg('submitURL'));
    }

    /**
     * Обновляет баннер в БД
     * @return void
     */
    private function update()
    {
        $Eresus = Eresus_CMS::getLegacyKernel();

        $item = $Eresus->db->selectItem($this->table['name'], "`id`='" . arg('update') . "'");
        $old_file = $item['image'];
        $item['caption'] = arg('caption', 'dbsafe');
        $item['active'] = arg('active', 'int');
        if (arg('section'))
        {
            $item['section'] = '|' . implode('|', arg('section')) . '|';
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
            $path = $Eresus->fdata . $this->name . '/';
            if (is_file($path . $old_file))
            {
                unlink($path . $old_file);
            }
            $filename = 'banner' . $item['id'] .
                substr($_FILES['image']['name'], strrpos($_FILES['image']['name'], '.'));
            upload('image', $path . $filename);
            $item['image'] = $filename;
        }

        $Eresus->db->updateItem($this->table['name'], $item, "`id`='" . $item['id'] . "'");

        if ($item['showTill'] == '')
        {
            $Eresus->db->query(
                "UPDATE " . $Eresus->db->prefix . $this->table['name'] .
                " SET `showTill` = NULL WHERE `id`='" . $item['id'] . "'");
        }

        HTTP::redirect(arg('submitURL'));
    }

    /**
     * Переключает активность баннера
     *
     * @param int $id
     * @return void
     */
    private function toggle($id)
    {
        $Eresus = Eresus_CMS::getLegacyKernel();

        $item = $Eresus->db->selectItem($this->table['name'], "`id`='" . $id . "'");
        $item['active'] = !$item['active'];

        $item = $Eresus->db->escape($item);
        $Eresus->db->updateItem($this->table['name'], $item, "`id`='" . $id . "'");

        HTTP::redirect(Eresus_Kernel::app()->getPage()->url());
    }

    /**
     * Удаляет баннер
     *
     * @param int $id
     * @return void
     */
    private function delete($id)
    {
        $Eresus = Eresus_CMS::getLegacyKernel();

        $item = $Eresus->db->selectItem($this->table['name'], "`id`='" . $id . "'");
        $path = $Eresus->fdata . $this->name . '/';
        if (
            !empty($item['image']) &&
            file_exists($path . $item['image'])
        )
        {
            unlink($path . $item['image']);
        }
        $Eresus->db->delete($this->table['name'], "`" . $this->table['key'] . "`='" . $id . "'");
        HTTP::redirect(str_replace('&amp;', '&', Eresus_Kernel::app()->getPage()->url()));
    }

    /**
     * Возвращает диалог добавления баннера
     *
     * @return string  HTML
     */
    private function create()
    {
        $sections = $this->menuBranch();
        array_unshift($sections[0], 'ВСЕ РАЗДЕЛЫ');
        array_unshift($sections[1], 'all');
        $form = array(
            'name' => 'formCreate',
            'caption' => 'Добавить баннер',
            'width' => '600px',
            'fields' => array(
                array('type' => 'hidden', 'name' => 'action', 'value' => 'insert'),
                array('type' => 'edit', 'name' => 'caption', 'label' => '<b>Заголовок</b>',
                    'width' => '100%', 'maxlength' => '255', 'pattern' => '/.+/',
                    'errormsg' => 'Заголовок не может быть пустым!'),
                array('type' => 'listbox', 'name' => 'section', 'label' => '<b>Разделы</b>', 'height' => 5,
                    'items' => $sections[0], 'values' => $sections[1]),
                array('type' => 'edit', 'name' => 'block', 'label' => '<b>Блок баннера</b>',
                    'width' => '100px', 'maxlength' => 31,
                    'comment' => 'Для вставки баннера используйте макрос <b>$(Banners:имя_блока)</b>',
                    'pattern' => '/.+/', 'errormsg' => 'Не указан блок баннера!'),
                array('type' => 'edit', 'name' => 'priority', 'label' => 'Приоритет', 'width' => '20px',
                    'comment' => 'Если для одного раздела и одного блока задано несколько баннеров, будет ' .
                        'показан с большим приоритетом',
                    'default' => 0, 'pattern' => '/\d+/', 'errormsg' => 'Приоритет задается только цифрами!'),
                array('type' => 'edit', 'name' => 'showFrom', 'label' => 'Начало показов',
                    'width' => '100px', 'comment' => 'ГГГГ-ММ-ДД', 'default' => gettime('Y-m-d'),
                    'pattern' => '/[12]\d{3,3}-[01]\d-[0-3]\d/', 'errormsg' => 'Неправильный формат даты!'),
                array('type' => 'edit', 'name' => 'showTill', 'label' => 'Конец показов',
                    'width' => '100px', 'comment' => 'ГГГГ-ММ-ДД; Пустое - без ограничений',
                    'pattern' => '/([12]\d{3,3}-[01]\d-[0-3]\d)|(^$)/',
                    'errormsg' => 'Неправильный формат даты!'),
                array('type' => 'edit', 'name' => 'showCount', 'label' => 'Макс. кол-во показов',
                    'width' => '100px', 'comment' => '0 - без ограничений', 'default' => 0,
                    'pattern' => '/(\d+)|(^$)/', 'errormsg' => 'Кол-во показов задается только цифрами!'),
                /*array ('type' => 'edit', 'name' => 'mail', 'label' => 'e-mail владельца',
                    'width' => '200px', 'maxlength' => '63'),*/
                array('type' => 'checkbox', 'name' => 'active', 'label' => 'Активировать',
                    'default' => true),
                array('type' => 'header', 'value' => 'Свойства баннера'),
                array('type' => 'file', 'name' => 'image', 'label' => 'Картинка или Flash', 'width' => '50'),
                array('type' => 'edit', 'name' => 'width', 'label' => 'Ширина', 'width' => '100px',
                    'comment' => 'только для Flash'),
                array('type' => 'edit', 'name' => 'height', 'label' => 'Высота', 'width' => '100px',
                    'comment' => 'только для Flash'),
                array('type' => 'edit', 'name' => 'url', 'label' => 'URL для ссылки', 'width' => '100%',
                    'maxlength' => '255'),
                array('type' => 'select', 'name' => 'target', 'label' => 'Открывать',
                    'items' => array('в новом окне', 'в том же окне')),
                array('type' => 'header', 'value' => 'HTML-код баннера'),
                array('type' => 'memo', 'name' => 'html',
                    'label' => 'HTML-код (Если задан HTML-код, то предыдущие свойства игнорируются и могут ' .
                        'не заполняться)',
                    'height' => '4'),
            ),
            'buttons' => array('ok', 'cancel'),
        );

        /** @var TAdminUI $page */
        $page = Eresus_Kernel::app()->getPage();
        $result = $page->renderForm($form);
        return $result;
    }

    /**
     * Возвращает диалог изменения баннера
     *
     * @return string  HTML
     */
    private function edit()
    {
        $Eresus = Eresus_CMS::getLegacyKernel();
        $item = $Eresus->db->selectItem($this->table['name'], "`id`='" . arg('id') . "'");
        $item['section'] = explode('|', $item['section']);
        $sections = $this->menuBranch();
        array_unshift($sections[0], 'ВСЕ РАЗДЕЛЫ');
        array_unshift($sections[1], 'all');
        $form = array(
            'name' => 'formEdit',
            'caption' => 'Изменить баннер',
            'width' => '95%',
            'fields' => array(
                array('type' => 'hidden', 'name' => 'update', 'value' => $item['id']),
                array('type' => 'edit', 'name' => 'caption', 'label' => '<b>Заголовок</b>',
                    'width' => '100%', 'maxlength' => '255', 'pattern' => '/.+/',
                    'errormsg' => 'Заголовок не может быть пустым!'),
                array('type' => 'listbox', 'name' => 'section', 'label' => '<b>Разделы</b>', 'height' => 5,
                    'items' => $sections[0], 'values' => $sections[1]),
                array('type' => 'edit', 'name' => 'block', 'label' => '<b>Блок баннера</b>',
                    'width' => '100px', 'maxlength' => 15,
                    'comment' => 'Для вставки баннера используйте макрос <b>$(Banners:имя_блока)</b>',
                    'pattern' => '/.+/', 'errormsg' => 'Не указан блок баннера!'),
                array('type' => 'edit', 'name' => 'priority', 'label' => 'Приоритет', 'width' => '20px',
                    'comment' => 'Если для одного раздела и одного блока задано несколько баннеров, будет ' .
                        'показан с большим приоритетом', 'default' => 0, 'pattern' => '/\d+/',
                    'errormsg' => 'Приоритет задается только цифрами!'),
                array('type' => 'edit', 'name' => 'showFrom', 'label' => 'Начало показов',
                    'width' => '100px', 'comment' => 'ГГГГ-ММ-ДД', 'default' => gettime('Y-m-d'),
                    'pattern' => '/[12]\d{3,3}-[01]\d-[0-3]\d/', 'errormsg' => 'Неправильный формат даты!'),
                array('type' => 'edit', 'name' => 'showTill', 'label' => 'Конец показов',
                    'width' => '100px', 'comment' => 'ГГГГ-ММ-ДД; Пустое - без ограничений',
                    'pattern' => '/(\d{4,4}-[01]\d-[0-3]\d)|(^$)/', 'errormsg' => 'Неправильный формат даты!'),
                array('type' => 'edit', 'name' => 'showCount', 'label' => 'Макс. кол-во показов',
                    'width' => '100px', 'comment' => '0 - без ограничений', 'default' => 0,
                    'pattern' => '/(\d+)|(^$)/', 'errormsg' => 'Кол-во показов задается только цифрами!'),
                //array ('type' => 'edit', 'name' => 'mail', 'label' => 'e-mail владельца',
                //'width' => '200px', 'maxlength' => '63'),
                array('type' => 'checkbox', 'name' => 'active', 'label' => 'Активировать'),
                array('type' => 'header', 'value' => 'Свойства баннера'),
                array('type' => 'file', 'name' => 'image', 'label' => 'Картинка или Flash', 'width' => '50',
                    'comment' => '<a></a>'),
                array('type' => 'edit', 'name' => 'width', 'label' => 'Ширина', 'width' => '100px',
                    'comment' => 'только для Flash'),
                array('type' => 'edit', 'name' => 'height', 'label' => 'Высота', 'width' => '100px',
                    'comment' => 'только для Flash'),
                array('type' => 'edit', 'name' => 'url', 'label' => 'URL для ссылки', 'width' => '100%',
                    'maxlength' => '255'),
                array('type' => 'select', 'name' => 'target', 'label' => 'Открывать',
                    'items' => array('в новом окне', 'в том же окне')),
                array('type' => 'header', 'value' => 'HTML-код баннера'),
                array('type' => 'memo', 'name' => 'html',
                    'label' => 'HTML-код (Если задан HTML-код, то предыдущие свойства игнорируются и могут' .
                        'не заполняться)',
                    'height' => '4'),
                array('type' => 'divider'),
                array('type' => 'checkbox', 'name' => 'flushShowCount',
                    'label' => 'Обнулить кол-во показов'),
            ),
            'buttons' => array('ok', 'apply', 'cancel'),
        );

        /** @var TAdminUI $page */
        $page = Eresus_Kernel::app()->getPage();
        $result = $page->renderForm($form, $item);
        return $result;
    }

    /**
     * Возвращает разметку списка баннеров
     *
     * @return string  HTML
     */
    public function adminRender()
    {
        /** @var TAdminUI $page */
        $page = Eresus_Kernel::app()->getPage();
        $result = '';
        if (arg('update'))
        {
            $this->update();
        }
        elseif (arg('toggle'))
        {
            $this->toggle(arg('toggle', 'int'));
        }
        elseif (arg('delete'))
        {
            $this->delete(arg('delete', 'int'));
        }
        elseif (arg('id'))
        {
            $result = $this->edit();
        }
        elseif (arg('action'))
        {
            switch (arg('action'))
            {
                case 'create':
                    $result = $this->create();
                    break;
                case 'insert':
                    $this->insert();
                    break;
            }
        }
        else
        {
            $result = $page->renderTable($this->table);
        }
        return $result;
    }

    /**
     *
     * @return void
     */
    public function adminOnMenuRender()
    {
        /** @var TAdminUI $page */
        $page = Eresus_Kernel::app()->getPage();

        $page->addMenuItem(admExtensions, array(
            'access' => EDITOR,
            'link' => $this->name,
            'caption' => $this->title,
            'hint' => $this->description
        ));
    }

    /**
     * Отрисовка баннеров и обработка кликов
     *
     * @param string $text HTML страницы
     * @return string  HTML страницы
     */
    public function clientOnPageRender($text)
    {
        $Eresus = Eresus_CMS::getLegacyKernel();
        /** @var TClientUI $page */
        $page = Eresus_Kernel::app()->getPage();
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
            $repo = Banners_Repository::getInstance();
            // Ищем все места вставки баннеров
            preg_match_all('/\$\(Banners:([^)]+)\)/', $text, $blocks,
                PREG_SET_ORDER | PREG_OFFSET_CAPTURE);
            $delta = 0;
            foreach ($blocks as $block)
            {
                // Получаем баннеры для этого блока в порядке уменьшения приоритета
                $query = $repo->getQuery();
                $query
                    ->activeOnly()
                    ->forSection($page->id)
                    ->forBlock($block[1][0])
                    ->orderBy('priority', ezcQuerySelect::DESC); // TODO!
                $banners = $query->fetchAll();
                if (count($banners))
                {
                    /* Отсекаем баннеры с низким приоритетом */
                    $priority = $banners[0]->getPriority();
                    for ($i = 0; $i < count($banners); $i++)
                    {
                        if ($banners[$i]->getPriority() != $priority)
                        {
                            $banners = array_slice($banners, 0, $i);
                            break;
                        }
                    }

                    // Выбираем случайный баннер
                    $banner = $banners[mt_rand(0, count($banners) - 1)];
                    $banner->incShows();
                    $repo->save($banner);

                    $code = $banner->render();
                    $text = substr_replace($text, $code, $block[0][1] + $delta, strlen($block[0][0]));
                    $delta += strlen($code) - strlen($block[0][0]);
                }
            }
            $items = $Eresus->db->select($this->table['name'],
                "`active` = 1 AND (`showCount` != 0 AND `shows` > `showCount`) AND ((`showTill` < '" .
                gettime() . "') AND (`showTill` != '0000-00-00'))");
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

    /**
     * Возвращает объект хранилища баннеров
     *
     * @return Banners_Repository
     *
     * @since 3.00
     */
    public function getRepository()
    {
        return Banners_Repository::getInstance();
    }

    /**
     * Перенаправляет посетителя на URL, заданный баннером
     *
     * @param int $id Идентификатор баннера
     */
    private function processClick($id)
    {
        $repo = Banners_Repository::getInstance();
        $banner = $repo->find($id);
        /** @var TClientUI $page */
        $page = Eresus_Kernel::app()->getPage();
        if ($banner)
        {
            $banner->incClicks();
            $repo->save($banner);

            $url = $page->replaceMacros($banner->getURL());

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
            $page->httpError(404);
        }
    }

    /**
     * Создаёт таблицу в БД
     *
     * @param array $table
     *
     * @return void
     */
    protected function createTable($table)
    {
        $db = Eresus_CMS::getLegacyKernel()->db;
        $db->query("CREATE TABLE IF NOT EXISTS `{$db->prefix}{$table['name']}` {$table['sql']}");
    }
}

