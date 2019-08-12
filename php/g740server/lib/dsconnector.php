<?php
/**
 * @file
 * G740Server, шлюз доступа к данным, для удобной подмены доступа в зависимости от используемой системы разработки
 *
 * @copyright 2018-2019 Galinsky Leonid lenq740@yandex.ru
 * This project is released under the BSD license
 */

require_once('lib-base.php');
require_once('perm-controller.php');

/** Класс, интегрирующий функционал доступа к базе, через PDODataConnector
 *
 * Это шлюз для доступа к данным. Исходная проблема - налючие в системах вроде Laravel
 * своего механзма доступа к данным, для интеграции с ним достаточно подменить этот класс
 * и у всех потомков будет доступ к данным
 */
class DSConnector {
/// Имя соединения, актуально если соединений несколько
	public $pdoName='default';
/** Получить PDO соединение с базой данных
 *
 * @return	PDODataConnector PDO соединение
 */
	public function getPDO() {
		return getPDO($this->pdoName);
	}
/** Получить имя драйвера SQL сервера
 *
 * в настоящее время доступны варианты mysql, pgsql, sqlsrv
 * @return	string имя драйвера SQL сервера
 */
	public function getDriverName() {
		$pdoDB=$this->getPDO();
		return $pdoDB->getDriverName();
	}
/** Подготовить строку для корректной вставки в SQL запрос
 *
 * @param	string	$str строка
 * @return	string строка подготовленная для корректной вставки в SQL запрос
 */
	public function str2Sql($str) {
		$pdoDB=$this->getPDO();
		return $pdoDB->str2Sql($str);
	}
/** Подготовить строку для корректной вставки в SQL запрос в раздел like секции where
 *
 * @param	string	$str строка
 * @return	string строка подготовленная для корректной вставки в SQL запрос
 */
	public function str2SqlLike($str) {
		$pdoDB=$this->getPDO();
		return $pdoDB->str2SqlLike($str);
	}
/** Подготовить выражение PHP для корректной вставки в SQL запрос
 *
 * @param	anytype	$value выражение
 * @return	string строка подготовленная для корректной вставки в SQL запрос
 */
	public function php2Sql($value) {
		$pdoDB=$this->getPDO();
		return $pdoDB->php2Sql($value);
	}
/** Подготовить GUID для корректной вставки в SQL запрос
 *
 * @param	GUID	$str
 * @return	string строка подготовленная для корректной вставки в SQL запрос
 */
	public function guid2Sql($str) {
		$pdoDB=$this->getPDO();
		return $pdoDB->guid2Sql($str);
	}
/** Подготовить выражение PHP для корректной вставки в SQL запрос в раздел in секции where
 *
 * @param	anytype	$value выражение
 * @return	string строка подготовленная для корректной вставки в SQL запрос в раздел in секции where
 */
	public function php2SqlIn($value) {
		$pdoDB=$this->getPDO();
		return $pdoDB->php2SqlIn($value);
	}
/** Выполнить SQL запрос
 *
 * @param	string	$sql			SQL запрос
 * @param	string	$errorMessage	Текст сообщения об ошибке
 * @param	Array	$params			Параметры для подстановки в SQL запрос
 * @return	PDOStatement результат выполнения SQL запроса
 */
	public function pdo($sql, $errorMessage='', $params=Array()) {
		$pdoDB=$this->getPDO();
		return $pdoDB->pdo($sql, $errorMessage, $params);
	}
/** Вернуть ассоциативный массив строки результата выполнения запроса в виде 'имя поля' => 'значение поля'
 *
 * @param	anytype	$sql			строка SQL запроса или PDOStatement результата
 * @param	string	$errorMessage	Текст сообщения об ошибке
 * @param	Array	$params			Параметры для подстановки в SQL запрос
 * @return	Array ассоциативный массив результата выполнения SQL запроса
 */
	public function pdoFetch($sql, $errorMessage='', $params=Array()) {
		$pdoDB=$this->getPDO();
		return $pdoDB->pdoFetch($sql, $errorMessage, $params);
	}
/** Вернуть кол-во строк в результате последнего запроса
 *
 * @return	num кол-во строк запроса
 */
	public function pdoRowCount() {
		$pdoDB=$this->getPDO();
		return $pdoDB->rowCount();
	}
/** Завершить текущую транзакцию и начать новую
 */
	public function commitTransaction() {
		$pdoDB=$this->getPDO();
		if ($pdoDB->inTransaction()) {
			$pdoDB->commit();
			$pdoDB->beginTransaction();
		}
	}
}

/** Получить объект соединения по имени
 *
 * @param	string	$name имя соединения
 * @return	PDODataConnector объект соединения
 */
function getPDO($name='default') {
	global $_registerPDO;
	$result=$_registerPDO[$name];
	if (!$result) throw new Exception("Недопустимое имя соединения '{$name}'");
	return $result;
}
/** Зарегистрировать объект соединения по имени
 *
 * @param	PDODataConnector	$pdoDB объект соединения
 * @param	string				$name имя соединения
 */
function regPDO($pdoDB, $name='default') {
	global $_registerPDO;
	$_registerPDO[$name]=$pdoDB;
}
/// Пул активных соединений с разными базами данных
$_registerPDO=Array();
