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
/// Возможность запускать с правами root - но только из localhost
	public $isCanExecutedAsRoot=true;
/// Наименование утилиты
	public $caption='Пересчет';

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
		{
			echo <<<HTML
<div class="section">
<h2>Очистка старых log файлов</h2>
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