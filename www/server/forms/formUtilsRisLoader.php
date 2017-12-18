<?php
// Экранная форма FormUtilsRisLoader
class FormUtilsRisLoader extends FormController {
	protected function getDefinitionTemplate($params=Array(), $macro=Array()) {
		$form=$params['#request.form'];
		$url="server/risloader.php?klsris={$params['filter.klsris']}&amp;rissizecode={$params['filter.rissizecode']}";
		$result=<<<XML
<form name="{$form}" caption="Загрузка иллюстраций" modal="1">
	<panels>
		<panel type="webbrowser" url="{$url}">
		</panel>
	</panels>
</form>
XML;
		return $result;
	}
}
return new FormUtilsRisLoader();
