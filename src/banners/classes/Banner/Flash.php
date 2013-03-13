<?php
/**
 * Flash-баннер
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
 */


/**
 * Flash-баннер
 *
 * @package Banners
 */
class Banners_Banner_Flash extends Banners_Banner_Abstract
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
}
