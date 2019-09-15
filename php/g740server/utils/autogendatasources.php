<?php
/**
 * @file
 * G740Server, утилита автоматической генерации классов DataSource
 *
 * @copyright 2018-2019 Galinsky Leonid lenq740@yandex.ru
 * This project is released under the BSD license
 */
 
includeLib('dsautogenerator.php');

/// Класс контроллера утилиты AutogenDataSources
class UtilityAutogenDataSources extends UtilController {
/// Наименование утилиты
	public $caption='Генератор классов DataSource';
	
/** Формирование результата запроса
 *
 * @param	Array	$params параметры
 * @return	String	результат
 */
	public function go($params=Array()) {
		if (!getPerm(getCfg('perm.utils.autogendatasources','root'),'write')) throw new Exception('У Вас нет прав на генерацию классов DataSource');
		echo '<div class="section">'; flush();
		echo '<h2>Генерация классов DataSource</h2>'; flush();
		$params['echo']=true;
		$objAutoGenerator=new DSAutoGenerator($params);
		$objAutoGenerator->goDataSources($params);
		unset($objAutoGenerator);
		echo 'Ok!</div>'; flush();
		echo '<script>document.body.scrollIntoView(false)</script>'; flush();
	}
}
return new UtilityAutogenDataSources();
