<?php
/**
 * @file
 * G740Server, утилита плановых работ, выполняется по расписанию
 *
 * @copyright 2018-2019 Galinsky Leonid lenq740@yandex.ru
 * This project is released under the BSD license
 */

/// Класс контроллера утилиты плановых работ, выполняется по расписанию
class UtilityCron extends UtilController {
/** Разбор входных параметров
 *
 * @return	Array	параметры
 */
	public function getParams() {
		$params=Array();
		return $params;
	}
/** Формирование результата запроса
 *
 * @param	Array	$params параметры
 * @return	String	результат
 */
	public function go($params=Array()) {
		echo '<h2>Плановые работы</h2>';
		flush();
		{
			echo <<<HTML
<div class="section">
<h3>Запуск пересчета и обслуживания базы</h3>
HTML;
			flush();
			
			echo '<div class="message">Запускаем пересчет и ждем его завершения ... '; flush();
			execUtility('rebuild',Array('root'=>1),true);
			echo 'Ok!</div>'; flush();
			echo '<script>document.body.scrollIntoView(false)</script>'; flush();
			
			echo '</div>'; flush();
			echo '<script>document.body.scrollIntoView(false)</script>'; flush();
		}
	}
}
return new UtilityCron();