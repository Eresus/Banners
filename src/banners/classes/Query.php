<?php
/**
 * Запрос к хранилищу баннеров
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
 *
 * $Id$
 */

/**
 * Запрос к хранилищу баннеров
 *
 * Класс предназначен для формирования запроса на получение баннеров из БД
 *
 * @package Banners
 *
 * @since 3.00
 */
class Banners_Query
{
	/**
	 * Хранилище баннеров
	 *
	 * @var Banners_Repository
	 */
	protected $repo;

	/**
	 * Оригинальный запрос
	 *
	 * @var ezcQuerySelect
	 */
	protected $query;

	/**
	 * Набор условий для WHERE
	 *
	 * При формировании запроса они будут соединены оператором AND
	 *
	 * @var array
	 */
	protected $where = array();

	/**
	 * Конструктор
	 *
	 * @param Banners_Repository $repo  хранилище баннеров
	 *
	 * @return Banners_Query
	 */
	public function __construct(Banners_Repository $repo)
	{
		$this->repo = $repo;
		$this->query = DB::createSelectQuery()->select('*')->from('banners');
	}
	//-----------------------------------------------------------------------------

	/**
	 * Часть шаблона «Декоратор»
	 *
	 * @param string $name
	 *
	 * @return mixed
	 */
	public function __get($name)
	{
		return $this->query->{$name};
	}
	//-----------------------------------------------------------------------------

	/**
	 * Часть шаблона «Декоратор»
	 *
	 * @param string $name
	 * @param mixed  $value
	 *
	 * @return void
	 */
	public function __set($name, $value)
	{
		$this->query->{$name} = $value;
	}
	//-----------------------------------------------------------------------------

	/**
	 * Часть шаблона «Декоратор»
	 *
	 * @param string $name
	 * @param array  $args
	 *
	 * @return mixed
	 */
	public function __call($name, $args)
	{
		$result = call_user_func_array(array($this->query, $name), $args);
		if ($result === $this->query)
		{
			$result = $this;
		}
		return $result;
	}
	//-----------------------------------------------------------------------------

	/**
	 * Устанавливает значение для оператора WHERE
	 *
	 * @param string $sql
	 *
	 * @return Banners_Query  текучий интерфейс
	 */
	public function where($sql)
	{
		$this->where = array($sql);
		return $this;
	}
	//-----------------------------------------------------------------------------

	/**
	 * Выбрать баннеры, привязанные к определённому разделу сайта
	 *
	 * @param int $section  раздел сайта
	 *
	 * @return Banners_Query  текучий интерфейс
	 */
	public function forSection($section)
	{
		$this->where []= "(section LIKE '%|$section|%' OR `section` LIKE '%|all|%')";
		return $this;
	}
	//-----------------------------------------------------------------------------

	/**
	 * Выбрать баннеры, привязанные к определённому блоку
	 *
	 * @param string $block  имя блока
	 *
	 * @return Banners_Query  текучий интерфейс
	 */
	public function forBlock($block)
	{
		$this->where []= "block = '$block'";
		return $this;
	}
	//-----------------------------------------------------------------------------

	/**
	 * Выбрать только активные баннеры
	 *
	 * Т. е. баннеры с установленным флагом «Активность» и срок которых начался и не истёк.
	 *
	 * @return Banners_Query  текучий интерфейс
	 */
	public function activeOnly()
	{
		$this->where []= "(showFrom <= '" . gettime() . "')";
		$this->where []= "(showCount = 0 OR (shows < showCount) OR shows IS NULL)";
		$this->where []= "(showTill = '0000-00-00' OR showTill IS NULL OR showTill > '" . gettime() .
			"')";
		$this->where []= '(active = 1)';
		return $this;
	}
	//-----------------------------------------------------------------------------

	/**
	 * Возвращает все баннеры, удовлетворяющие запросу
	 *
	 * @return array
	 */
	public function fetchAll()
	{
		$this->query->where(implode(' AND ', $this->where));
		$records = DB::fetchAll($this->query);
		$banners = array();
		foreach ($records as $record)
		{
			$banner = Banners_Factory::createFromArray($record);
			$banner = $this->repo->registerObject($banner);
			$banners []= $banner;
		}
		return $banners;
	}
	//-----------------------------------------------------------------------------

	/**
	 * Возвращает один баннер, удовлетворяющий запросу
	 *
	 * @return Banners_Banner_Abstract|null
	 */
	public function fetch()
	{
		$this->query->where(implode(' AND ', $this->where));
		$record = DB::fetch($this->query);

		if (!$record)
		{
			return null;
		}

		$banner = Banners_Factory::createFromArray($record);
		return $banner;
	}
	//-----------------------------------------------------------------------------
}
