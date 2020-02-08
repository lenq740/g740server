<?php
/**
 * @file
 * G740Server, пример реализации страницы 404
 *
 * @copyright 2018-2020 Galinsky Leonid lenq740@yandex.ru
 * This project is released under the BSD license
 */

/** Класс PageViewer - пример реализации страницы 404
 */
class PageViewer404Exampl extends PageViewer {
/** Содержимое страницы
 *
 * @param	Array	$params
 * @return	String	Содержимое страницы
 */
	protected function getPage($params=Array()) {
		$result=<<<HTML
Упс... Ничего не найдено, ошибка 404
HTML;
		return $result;
	}
/** HTTP заголовки ответа
 *
 * @param	Array	$params
 */
	protected function sendHttpHeaders($params=Array()) {
		header('Content-type: text/text; charset=utf-8');
		header('HTTP/1.0 404 Not Found');
		header('HTTP/1.1 404 Not Found');
		header('Status: 404 Not Found');
	}
}
return new PageViewer404Exampl();