<?php
/**
 * @file
 * G740Server, пример реализации страницы redirect
 *
 * @copyright 2018-2020 Galinsky Leonid lenq740@yandex.ru
 * This project is released under the BSD license
 */

/** Класс PageViewer - пример реализации страницы redirect
 *
 * - params['href'] - адрес страницы редиректа
 * - params['code'] - код редиректа, по умолчанию 303 (временный, не кэшируется)
 */
class PageViewerRedirectExampl extends PageViewer {
/** Отобразить страницу
 *
 * @param	Array	$params
 */
	public function go($params=Array()) {
		$href=$params['href'];
		$code=$params['code'];
		if (!$code) $code='303';
		if (!$href) throw new Exception('Ошибка при обращении к PageViewerRedirectExampl.go(), не задан href');
		header('Location: '.$href, true, $code);
	}
}
return new PageViewerRedirectExampl();