<?php
/**
 * @file
 * G740Server, утилита плановых работ, выполняется по расписанию
 *
 * @copyright 2018-2020 Galinsky Leonid lenq740@yandex.ru
 * This project is released under the BSD license
 */

/// Класс контроллера утилиты плановых работ, выполняется по расписанию
class UtilityCron extends UtilController {
/// Возможность запускать с правами root - но только из localhost
	public $isCanExecutedAsRoot=true;
/// Наименование утилиты
	public $caption='Плановые работы';

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
		echo getUtilityResult('rebuild');
	}
}
return new UtilityCron();