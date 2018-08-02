<?php
/**
 * @file
 * Описание страницы admin
 */

/** Класс PageViewerRedirect
 */
class PageViewerAdmin extends PageViewer {
	public $mode='admin';
/** Разбор параметров для запроса к странице
 *
 * @param	Array	$lstUrl	массив адресов запроса
 * @return	Array	начитанные параметры
 */
	public function getInputParams($lstUrl=Array()) {
		$result=Array();
		$result['mode']='admin';
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
		$info=Array();
		$info['title']='Заготовка проекта';
		$info['favicon']='favicon.png';
		$info['path-g740client']='/'.pathConcat(
			getCfg('href.root'),
			getCfg('path.root.resource'),
			getCfg('path.root.resource.prjlib'),
			'g740client'
		);
		$info['path-g740icons-css']='/'.pathConcat(
			getCfg('href.root'),
			getCfg('path.root.resource'),
			getCfg('path.root.resource.prjlib'),
			'icons',
			'icons.css'
		);

		$info['iconset']='default';
		$info['config-appColorScheme']='red';
		$info['config-iconSizeDefault']='small';
		$info['config-urlServer']='/'.pathConcat(getCfg('href.root'),getCfg('path.root.server'),'index.php');
		$info['config-mainFormName']='formMainWithMenuBar';
		$info['config-login-isReloadBeforeLogin']=true;
		$info['config-login-loginUrl']='/'.pathConcat(
			getCfg('href.root'),
			getCfg('path.root.resource'),
			getCfg('path.root.resource.prjlib'),
			'logoscreen'
		);
		$result=getPageControllerG740Client($info, getCfg('g740.icons'));
		return $result;
	}
}
return new PageViewerAdmin();