<?php
/**
 * @file
 * G740Server, контроллер экранной формы утилиты
 *
 * @copyright 2018-2019 Galinsky Leonid lenq740@yandex.ru
 * This project is released under the BSD license
 */
 
/** FormController экранной формы утилиты
 *
 * Все переданные параметры переадресуются контроллеру утилиты
 */
class FormUtils extends FormController {
/** формируем XML описание экранной формы
 *
 * @param	Array	$params контекст вызова
 * @param	Array	$macro макроподстановки
 * @return	string XML описание экранной формы
 */
	protected function getDefinitionTemplate($params=Array(), $macro=Array()) {
		$form=$params['#request.form'];
		$url=pathConcat(getCfg('href.root','/'),'utils.php');
		
		$delimiter='?';
		foreach($params as $key=>$value) {
			if (substr($key,0,1)=='#') continue;
			if (!$value) continue;
			$url.=$delimiter.str2Attr($key).'='.str2Attr($value);
			$delimiter='&amp;';
		}
		$result=<<<XML
<form name="{$form}" caption="Утилиты" modal="1">
	<panels>
		<panel>
			<panel type="webbrowser" url="{$url}"/>
			<buttons>
				<request name="close" icon="ok" caption="Закрыть" align="right"/>
			</buttons>
		</panel>
	</panels>
</form>
XML;
		return $result;
	}
}
return new FormUtils();
