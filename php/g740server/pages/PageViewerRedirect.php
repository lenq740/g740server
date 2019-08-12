<?php
/**
 * @file
 * Описание страницы redirect
 */

/** Класс PageViewerRedirect
 */
class PageViewerRedirect extends PageViewer {
/// имя визуализатора страницы
	public $mode='redirect';
/** Разбор параметров для запроса к странице
 *
 * @param	Array	$lstUrl	массив адресов запроса
 * @return	Array	начитанные параметры
 */
	public function getInputParams($lstUrl=Array()) {
		$result=Array();
		if ($lstUrl[0]=='files') return $result;
		if ($lstUrl[0]=='resource') return $result;
		
		if (!getPerm('connected') && $lstUrl[0]!='login') {
			$result['mode']='redirect';
			$result['href']=getHref(Array('mode'=>'login'));
		}
		else if (getPerm('admin-control-panel','read') && $lstUrl[0]!='admin') {
			$result['mode']='redirect';
			$result['href']=getHref(Array('mode'=>'admin'));
		}
		return $result;
	}
/** Получить адрес ссылки по параметрам
 *
 * @param	Array	$params
 * @return	String	адрес ссылки по параметрам
 */
	public function getHref($params=Array()) {
		return '';
	}
/** Содержимое страницы
 *
 * @param	Array	$params
 * @return	String	Содержимое страницы
 */
	public function getPage($params=Array()) {
		return '';
	}
/** Отослать HTTP заголовки ответа
 *
 * @param	Array	$params
 */
	public function sendHttpHeaders($params=Array()) {
		$href=$params['href'];
		$code=$params['code']?$params['code']:'303';
		header('Location: '.$href, true, $code);
	}
}
return new PageViewerRedirect();