<?php
// Экранная форма FormUtils
class FormUtils extends FormController {
	protected function getDefinitionTemplate($params=Array(), $macro=Array()) {
		$form=$params['#request.form'];
		$url="server/utils/utils.php";
		
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
		<panel type="webbrowser" url="{$url}">
		</panel>
	</panels>
</form>
XML;
		return $result;
	}
}
return new FormUtils();
?>