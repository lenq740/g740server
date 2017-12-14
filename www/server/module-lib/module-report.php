<?php
/**
Утилиты для пересчета вычисляемых полей, и перегенерации файлов
@package module-lib
@subpackage module-report
*/

/**
Класс предок для утилит
@package module-lib
@subpackage module-report
*/
class ReportController {
	// разбор параметров
	public function go() {
		$pdoDB=getPDO();
		$params=Array();
		$mode=$_REQUEST['mode'];
		if ($mode=='backup') {
			$params['isdatastru']=$pdoDB->str2Sql($_REQUEST['isdatastru']);
			$params['issystem']=$pdoDB->str2Sql($_REQUEST['issystem']);
			$params['isstatic']=$pdoDB->str2Sql($_REQUEST['isstatic']);
			$params['isdynamic']=$pdoDB->str2Sql($_REQUEST['isdynamic']);
			if ($_REQUEST['isall']) {
				$params['isdatastru']=1;
				$params['issystem']=1;
				$params['isstatic']=1;
				$params['isdynamic']=1;
			}
			$this->goBackup($params, true);
		}
		if ($mode=='restore') {
			$params['isdatastru']=$pdoDB->str2Sql($_REQUEST['isdatastru']);
			$params['issystem']=$pdoDB->str2Sql($_REQUEST['issystem']);
			$params['isstatic']=$pdoDB->str2Sql($_REQUEST['isstatic']);
			$params['isdynamic']=$pdoDB->str2Sql($_REQUEST['isdynamic']);
			if ($_REQUEST['isall']) {
				$params['isdatastru']=1;
				$params['issystem']=1;
				$params['isstatic']=1;
				$params['isdynamic']=1;
			}
			$this->goRestore($params, true);
		}
		if ($mode=='autogendatasources') {
			$this->goAutogenDataSources($params, true);
		}
	}

	public function testReport() {
		$dsTrade=getDataStorage('trade');
		$itemsTrade=$dsTrade->getItems(Array('filter.id'=>Array(16,33,57,59)));
		$htmlTrade='';
		foreach($itemsTrade as $itemTrade) {
			
			$htmlImg='';
			foreach($itemTrade->getRefItems('img', true) as $itemImg) {
				if ($itemImg->get('mode')!=1) continue;
				if ($htmlImg) $htmlImg.="\n";
				$htmlImg.="<li>{$itemImg->getHtml('name')}</li>";
			}
			
			$htmlTrade.="\n".<<<HTML
<tr>
	<td>{$itemTrade->getId()}</td>
	<td>{$itemTrade->getHtml('name')}</td>
	<td>
		<ul>
{$htmlImg}
		</ul>
	</td>
	<td>
{$itemTrade->getRefItem('klsmktu2', true)->getHtml('code')}
	</td>
</tr>
HTML;
		}
		$result=<<<HTML
<h1>Пример отчета</h1>
<table class="table table-bordered">
<thead>
	<tr>
		<th>id</th>
		<th>Товар</th>
		<th>Иллюстрации</th>
		<th>МКТУ</th>
	</tr>
</thead>
{$htmlTrade}
</table>
HTML;
		return $result;
	}
}
$objReport=new ReportController();
?>