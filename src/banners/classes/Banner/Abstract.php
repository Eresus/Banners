<?php
/**
 * Абстрактный баннер
 *
 * @copyright 2011, Eresus Project, http://eresus.ru/
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
 *
 * $Id$
 */


/**
 * Абстрактный баннер
 *
 * @package Banners
 */
abstract class Banners_Banner_Abstract
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
	 * @return Banners_Banner_Abstract
	 */
	public function __construct($data)
	{
		$this->data = $data;
	}
	//-----------------------------------------------------------------------------

	/**
	 * Возвращает идентификатор баннера
	 *
	 * @return int
	 *
	 * @since 3.00
	 */
	public function getId()
	{
		return intval($this->data['id']);
	}
	//-----------------------------------------------------------------------------

	/**
	 * Возвращает название
	 *
	 * @return string
	 *
	 * @since 3.00
	 */
	public function getCaption()
	{
		return $this->data['caption'];
	}
	//-----------------------------------------------------------------------------

	/**
	 * Возвращает флаг активности
	 *
	 * @return bool
	 *
	 * @since 3.00
	 */
	public function getActive()
	{
		return (bool) $this->data['active'];
	}
	//-----------------------------------------------------------------------------

	/**
	 * Возвращает разделы в виде строки
	 *
	 * @return string
	 *
	 * @since 3.00
	 */
	public function getSectionStr()
	{
		return $this->data['section'];
	}
	//-----------------------------------------------------------------------------

	/**
	 * Возвращает приоритет
	 *
	 * @return int
	 *
	 * @since 3.00
	 */
	public function getPriority()
	{
		return intval($this->data['priority']);
	}
	//-----------------------------------------------------------------------------

	/**
	 * Возвращает имя блока
	 *
	 * @return string
	 *
	 * @since 3.00
	 */
	public function getBlock()
	{
		return $this->data['block'];
	}
	//-----------------------------------------------------------------------------

	/**
	 * Возвращает дату начала показов
	 *
	 * @return string
	 *
	 * @since 3.00
	 */
	public function getShowFrom()
	{
		return $this->data['showFrom'];
	}
	//-----------------------------------------------------------------------------

	/**
	 * Возвращает дату конца показов
	 *
	 * @return string
	 *
	 * @since 3.00
	 */
	public function getShowTill()
	{
		return $this->data['showTill'];
	}
	//-----------------------------------------------------------------------------

	/**
	 * Возвращает ограничение на количество показов
	 *
	 * @return int
	 *
	 * @since 3.00
	 */
	public function getShowCount()
	{
		return intval($this->data['showCount']);
	}
	//-----------------------------------------------------------------------------

	/**
	 * Возвращает код HTML
	 *
	 * @return string
	 *
	 * @since 3.00
	 */
	public function getHTML()
	{
		return $this->data['html'];
	}
	//-----------------------------------------------------------------------------

	/**
	 * Возвращает изображение
	 *
	 * @return string
	 *
	 * @since 3.00
	 */
	public function getImage()
	{
		return $this->data['image'];
	}
	//-----------------------------------------------------------------------------

	/**
	 * Возвращает ширину изображения
	 *
	 * @return string
	 *
	 * @since 3.00
	 */
	public function getWidth()
	{
		return $this->data['width'];
	}
	//-----------------------------------------------------------------------------

	/**
	 * Возвращает высоту изображения
	 *
	 * @return string
	 *
	 * @since 3.00
	 */
	public function getHeight()
	{
		return $this->data['height'];
	}
	//-----------------------------------------------------------------------------

	/**
	 * Возвращает URL
	 *
	 * @return string
	 *
	 * @since 3.00
	 */
	public function getURL()
	{
		return $this->data['url'];
	}
	//-----------------------------------------------------------------------------

	/**
	 * Надо ли открывать ссылку в новом окне?
	 *
	 * @return bool
	 *
	 * @since 3.00
	 */
	public function getTargetIsBlank()
	{
		return (bool) $this->data['target'];
	}
	//-----------------------------------------------------------------------------

	/**
	 * Возвращает количество просмотров
	 *
	 * @return int
	 *
	 * @since 3.00
	 */
	public function getShows()
	{
		return intval($this->data['shows']);
	}
	//-----------------------------------------------------------------------------

	/**
	 * Увеличивает количество просмотров на единицу
	 *
	 * @return void
	 *
	 * @since 3.00
	 */
	public function incShows()
	{
		$this->data['shows']++;
	}
	//-----------------------------------------------------------------------------

	/**
	 * Возвращает количество переходов
	 *
	 * @return int
	 *
	 * @since 3.00
	 */
	public function getClicks()
	{
		return intval($this->data['clicks']);
	}
	//-----------------------------------------------------------------------------

	/**
	 * Увеличивает количество кликов на единицу
	 *
	 * @return void
	 *
	 * @since 3.00
	 */
	public function incClicks()
	{
		$this->data['clicks']++;
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
