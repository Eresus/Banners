<?php
/**
 * Графический баннер
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
 * Графический баннер
 *
 * @package Banners
 */
class Banners_Banner_Image extends Banners_Banner_Abstract
{
    /**
     * Возвращает кода баннера для вставки на страницу
     *
     * @return string  HTML
     * @see AbstractBanner::render()
     */
    public function render()
    {
        $plugin = $this->getPlugin();

        $html = img($plugin->getDataDir() . $this->data['image']);

        if (!empty($this->data['url']))
        {
            $template = '<a href="%s"%s>%s</a>';

            $url = Eresus_CMS::getLegacyKernel()->request['path'] . '?banners-click='
                . $this->data['id'];
            $target = $this->data['target'] ? '' : ' target="_blank"';

            $html = sprintf($template, $url, $target, $html);
        }

        return $html;
    }
}

