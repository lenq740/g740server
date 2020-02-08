<?php
/**
 * @file
 * G740Server, пример простейшего PageUrlController
 *
 * @copyright 2018-2020 Galinsky Leonid lenq740@yandex.ru
 * This project is released under the BSD license
 */
 
/// пример простейшего PageUrlController
class PageUrlControllerExampl extends PageUrlController {
/** Разбор входных параметров страницы
 *
 * @return	Array	параметры PageViewer
 */
	public function getParams() {
		$result=Array();
		$lstUrl=$this->getLstUrl();
		$result['url']=implode('/',$lstUrl);
		try {
			if (mb_strtolower($lstUrl[0],'utf-8')=='resource') {
				$result['page']='404';
				return $result;
			}
			
			// login
			if (count($lstUrl)==1 && $lstUrl[0]=='login') {
				$result['page']='login';
				return $result;
			}
			if (!getPerm('connected')) {
				$result['page']='redirect';
				$result['href']=getPageHref(Array(
					'page'=>'login'
				));
				return $result;
			}
			// admin
			if (count($lstUrl)==1 && $lstUrl[0]=='admin') {
				$result['page']='admin';
				return $result;
			}
			if (getPerm('adm','read')) {
				$result['page']='redirect';
				$result['href']=getPageHref(Array(
					'page'=>'admin'
				));
				return $result;
			}
			
			// 404
			$result['page']='404';
		}
		catch(Exception $e) {
			$result['page']='error';
			$result['message']=$e->getMessage();
		}
		return $result;
	}
}
return new PageUrlControllerExampl();