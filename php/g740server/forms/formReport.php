<?php
/**
 * @file
 * G740Server, контроллер экранной формы стандартного отчета
 *
 * @copyright 2018-2020 Galinsky Leonid lenq740@yandex.ru
 * This project is released under the BSD license
 */

/** FormController экранной формы стандартного отчета
 *
 * Все переданные параметры переадресуются контроллеру отчетов
 */
class FormReport extends FormController {
/** формируем XML описание экранной формы
 *
 * @param	Array	$params контекст вызова
 * @param	Array	$macro макроподстановки
 * @return	string XML описание экранной формы
 */
	protected function getDefinitionTemplate($params=Array(), $macro=Array()) {
		$form=$params['#request.form'];
		$url=pathConcat(
			getCfg('href.root','/'),
			'reports.php'
		);
		$delimiter='?';
		foreach($params as $key=>$value) {
			if (substr($key,0,1)=='#') continue;
			if (!$value) continue;
			$url.=$delimiter.str2Attr($key).'='.str2Attr($value);
			$delimiter='&amp;';
		}
		$result=<<<XML
<form name="{$form}" caption="Отчет" icon="print">
	<panels>
		<panel>
			<panel type="webbrowser" url="{$url}"/>
		</panel>
	</panels>
</form>
XML;
		return $result;
	}
}
return new FormReport();