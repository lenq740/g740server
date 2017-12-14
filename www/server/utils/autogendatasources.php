<?php
// Утилита AutogenDataSources
class UtilityAutogenDataSources extends UtilController {
	public function getParams() {
		$pdoDB=getPDO();
		$params=Array();
		return $params;
	}
	public function go($params=Array(), $isEcho=false) {
		if (!getPerm('sys','write')) throw new Exception('У Вас нет прав на выполнение системных утилит, увы и ах...');
		if ($isEcho) {
			echo '<h2>Генерация классов DataSource</h2>'; flush();
			echo '<div class="section">'; flush();
		}
		$params['isEcho']=$isEcho;
		$objAutoGenerator=new AutoGenerator($params);
		$params['path']=getCfg('path.datasources').'/autogen/';
		$objAutoGenerator->goDataSources($params);
		unset($objAutoGenerator);
		if ($isEcho) {
			echo 'Ok!</div>'; flush();
			echo '<script>document.body.scrollIntoView(false)</script>'; flush();
		}
	}
}
return new UtilityAutogenDataSources();
?>