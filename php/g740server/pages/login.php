<?php
/**
 * @file
 * G740Server, заглушка вместо страницы login
 *
 * @copyright 2018-2019 Galinsky Leonid lenq740@yandex.ru
 * This project is released under the BSD license
 */

/** Класс PageViewer - заглушка вместо страницы login
 */
class PageViewerLoginExampl extends PageViewer {
/** href страницы в зависимости от параметров
 *
 * @param	Array	$params
 * @return	String	href страницы
 */
	public function getHref($params=Array()) {
		return pathConcat(
			getCfg('href.root'),
			'login'
		);
	}
/** Содержимое страницы
 *
 * @param	Array	$params
 * @return	String	Содержимое страницы
 */
	protected function getPage($params=Array()) {
		$result=<<<HTML
заглушка вместо страницы login
HTML;
		return $result;
	}
}
return new PageViewerLoginExampl();