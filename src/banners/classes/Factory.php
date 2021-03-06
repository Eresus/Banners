<?php
/**
 * Фабрика баннеров
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
 * Фабрика баннеров
 *
 * Класс предназначен для создания объектов баннеров
 *
 * @package Banners
 */
class Banners_Factory
{
    /**
     * Создаёт объект баннера из массива его свойств
     *
     * @param array $data
     * @return Banners_Banner_Abstract  Объект баннера
     */
    public static function createFromArray($data)
    {
        switch (true)
        {
            case $data['html'] != '':
                return new Banners_Banner_Text($data);
            case preg_match('/\.swf$/i', $data['image']):
                return new Banners_Banner_Flash($data);
            default:
                return new Banners_Banner_Image($data);
        }
    }
}

