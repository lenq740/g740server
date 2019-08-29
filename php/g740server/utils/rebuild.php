<?php
/**
 * @file
 * G740Server, утилита пересчета и обслуживания базы
 *
 * @copyright 2018-2019 Galinsky Leonid lenq740@yandex.ru
 * This project is released under the BSD license
 */
 
/// Класс контроллера утилиты пересчета и обслуживания базы
class UtilityRebuild extends UtilController {
/** Разбор входных параметров
 *
 * @return	Array	параметры
 */
	public function getParams() {
		$params=Array();
		if ($_REQUEST['root']==1) {
			if ($_SERVER['REMOTE_ADDR']=='127.0.0.1' || $_SERVER['REMOTE_ADDR']=='::1') execConnectAsRoot();
		}
		return $params;
	}
/** Формирование результата запроса
 *
 * @param	Array	$params параметры
 * @return	String	результат
 */
	public function go($params=Array()) {
		echo '<h2>Пересчет и обслуживание базы</h2>';
		flush();
		{
			echo <<<HTML
<div class="section">
<h3>Очистка старых log файлов</h3>
HTML;
			flush();
			echo '<div class="message">Очищаем старые log файлы . . . '; flush();
			deleteOldLogFiles();
			echo 'Ok!</div>'; flush();
			echo '<script>document.body.scrollIntoView(false)</script>'; flush();
			echo '</div>'; flush();
			echo '<script>document.body.scrollIntoView(false)</script>'; flush();
		}
	}
}
return new UtilityRebuild();