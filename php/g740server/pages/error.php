<?php
/**
 * @file
 * G740Server, пример реализации страницы error
 *
 * @copyright 2018-2019 Galinsky Leonid lenq740@yandex.ru
 * This project is released under the BSD license
 */

/** Класс PageViewer - пример реализации страницы error
 *
 * - params['message'] - текст ошибки
 *
 * Эта реализация страницы ошибки возвращает содержимое ошибки в виде текста - text/text
 */
class PageViewerErrorExampl extends PageViewer {
/** Содержимое страницы
 *
 * @param	Array	$params
 * @return	String	содержимое страницы
 */
	protected function getPage($params=Array()) {
		$message=$params['message'];
		$result=<<<HTML
При формировании страницы произошла ошибка:

{$message}
HTML;
		return $result;
	}
/** HTTP заголовки ответа
 *
 * @param	Array	$params
 */
	protected function sendHttpHeaders($params=Array()) {
		header('Content-type: text/text; charset=utf-8');
		header("Cache-Control: no-store, no-cache, must-revalidate");
		header("Cache-Control: post-check=0, pre-check=0", false);
	}
}
return new PageViewerErrorExampl();