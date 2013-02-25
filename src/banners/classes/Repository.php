<?php
/**
 * Хранилище баннеров
 *
 * @copyright 2012, Eresus Project, http://eresus.ru/
 * @license http://www.gnu.org/licenses/gpl.txt  GPL License 3
 * @author Михаил Красильников <mihalych@vsepofigu.ru>
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
 * Хранилище баннеров
 *
 * Класс предназначен для извлечения баннеров из БД
 *
 * @package Banners
 *
 * @since 3.00
 */
class Banners_Repository
{
	/**
	 * Одиночка
	 *
	 * @var Banners_Repository
	 * @since 3.00
	 */
	private static $instance = null;

	/**
	 * Реестр загруженных баннеров
	 *
	 * @var array
	 */
	private $registry = array();

	/**
	 * Возвращает экземпляр-одиночку
	 *
	 * @return Banners_Repository
	 * @since 3.00
	 */
	public static function getInstance()
	{
		if (null === self::$instance)
		{
			self::$instance = new self();
		}
		return self::$instance;
	}
	//-----------------------------------------------------------------------------

	/**
	 * Скрываем конструктор
	 */
	private function __construct()
	{
	}
	//-----------------------------------------------------------------------------

	/**
	 * Запрещаем клонирование
	 *
	 * @throws LogicException
	 */
	public function __clone()
	{
		throw new LogicException('Class ' . __CLASS__ . ' and it\'s descendants can\'t be cloned.');
	}
	//-----------------------------------------------------------------------------

	/**
	 * Помещает объект в реестр
	 *
	 * @param Banners_Banner_Abstract $banner
	 *
	 * @return Banners_Banner_Abstract  если в реестре уже есть объект этого же баннера, возвратит
	 *                                  уже существующий объект, иначе возвратит $banner
	 */
	public function registerObject(Banners_Banner_Abstract $banner)
	{
		if (isset($this->registry[$banner->getId()]))
		{
			return $this->registry[$banner->getId()];
		}

		$this->registry[$banner->getId()] = $banner;
		return $banner;
	}
	//-----------------------------------------------------------------------------

	/**
	 * Возвращает баннер с указанным ID
	 *
	 * @param int $id
	 *
	 * @return Banners_Banner_Abstract|null
	 */
	public function find($id)
	{
		$query = $this->getQuery();
		$query->where($query->expr->eq('id', $query->bindValue($id, null, PDO::PARAM_INT)));
		$banner = $query->fetch();

		return $banner;
	}
	//-----------------------------------------------------------------------------

	/**
	 * Возвращает объект для формирования запроса
	 *
	 * @return Banners_Query
	 */
	public function getQuery()
	{
		return new Banners_Query($this);
	}
	//-----------------------------------------------------------------------------

	/**
	 * Сохраняет изменения объекта в БД
	 *
	 * @param Banners_Banner_Abstract $banner
	 *
	 * @return void
	 */
	public function save(Banners_Banner_Abstract $banner)
	{
		$q = DB::createUpdateQuery();
		$q->update('banners')->
			set('caption', $q->bindValue($banner->getCaption(), null, PDO::PARAM_STR))->
			set('active', $q->bindValue($banner->getActive(), null, PDO::PARAM_BOOL))->
			set('section', $q->bindValue($banner->getSectionStr(), null, PDO::PARAM_STR))->
			set('priority', $q->bindValue($banner->getPriority(), null, PDO::PARAM_INT))->
			set('block', $q->bindValue($banner->getBlock(), null, PDO::PARAM_STR))->
			set('showFrom', $q->bindValue($banner->getShowFrom(), null, PDO::PARAM_STR))->
			set('showTill', $q->bindValue($banner->getShowTill(), null, PDO::PARAM_STR))->
			set('showCount', $q->bindValue($banner->getShowCount(), null, PDO::PARAM_INT))->
			set('html', $q->bindValue($banner->getHTML(), null, PDO::PARAM_STR))->
			set('image', $q->bindValue($banner->getImage(), null, PDO::PARAM_STR))->
			set('width', $q->bindValue($banner->getWidth(), null, PDO::PARAM_STR))->
			set('height', $q->bindValue($banner->getHeight(), null, PDO::PARAM_STR))->
			set('url', $q->bindValue($banner->getURL(), null, PDO::PARAM_STR))->
			set('target', $q->bindValue($banner->getTargetIsBlank(), null, PDO::PARAM_BOOL))->
			set('shows', $q->bindValue($banner->getShows(), null, PDO::PARAM_INT))->
			set('clicks', $q->bindValue($banner->getClicks(), null, PDO::PARAM_INT))->
			where($q->expr->eq('id', $q->bindValue($banner->getId(), null, PDO::PARAM_INT)));
		DB::execute($q);
	}
	//-----------------------------------------------------------------------------

	/**
	 * Возвращает объект плагина
	 *
	 * @return Banners
	 *
	 * @since 3.00
	 */
	protected function getPlugin()
	{
		return $GLOBALS['Eresus']->plugins->load('banners');
	}
	//-----------------------------------------------------------------------------
}

