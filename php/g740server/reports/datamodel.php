<?php
/**
 * @file
 * G740Server, отчет по модели данных
 *
 * @copyright 2018-2019 Galinsky Leonid lenq740@yandex.ru
 * This project is released under the BSD license
 */

/// Класс контроллера отчета по модели данных
class ReportDataModel extends ReportController {
/** Разбор входных параметров
 *
 * @return	Array	параметры
 */
	public function getParams() {
		includeLib('lib-report.php');
		$params=parent::getParams();
		return $params;
	}
/** Формирование текста отчета
 *
 * @param	Array	$params параметры
 * @return	String	текст отчета
 */
	public function getBody($params=Array()) {
		$format=$params['format'];
		
		$result=<<<HTML
<h1>Модель данных</h1>
HTML;

		$sqlFieldSystableName=<<<SQL
systable.tablename+' ('+systable.name+')' as systable_name
SQL;
		if ($this->getDriverName()=='pgsql' || $this->getDriverName()=='mysql') {
			$sqlFieldSystableName=<<<SQL
concat(systable.tablename, ' (', systable.name, ')') as systable_name
SQL;
		}
		
		$p=Array();
		$p['sql']=<<<SQL
select
	systablecategory.id as klssystablecategory,
	systablecategory.name as systablecategory_name,
	systable.id as klssystable,
	{$sqlFieldSystableName},
	sysfield.*,
	sysfieldtype.name as sysfieldtype_name,
	reftable.tablename as reftable_tablename
from
	systablecategory
		join systable on systable.klssystablecategory=systablecategory.id
		join sysfield on sysfield.klssystable=systable.id
		left join sysfieldtype on sysfieldtype.id=sysfield.klssysfieldtype
		left join systable reftable on reftable.id=sysfield.klsreftable
order by 
	systablecategory.ord, systablecategory.id,
	systable.tablename, systable.id,
	sysfield.ord, sysfield.id
SQL;

		$fields=Array();
		$fields[]=Array(
			'fieldname'=>'systablecategory_name',
			'tgroup.field'=>'klssystablecategory',
			'tgroup'=>1,
			'tgroup.itog'=>0,
			'caption'=>'Категория'
		);
		$fields[]=Array(
			'fieldname'=>'systable_name',
			'tgroup.field'=>'klssystable',
			'tgroup'=>1,
			'tgroup.itog'=>0,
			'caption'=>'Таблица'
		);
		$fields[]=Array(
			'fieldname'=>'fieldname',
			'caption'=>'Поле'
		);
		$fields[]=Array(
			'fieldname'=>'name',
			'caption'=>'Наименование'
		);
		$fields[]=Array(
			'fieldname'=>'sysfieldtype_name',
			'caption'=>'Тип'
		);
		$fields[]=Array(
			'fieldname'=>'reftable_tablename',
			'caption'=>'Связанная таблица'
		);
		$p['fields']=$fields;
		$p['format']=$format;

		$objReport=new ReportBuilder($p);
		$result.=$objReport->Get();
		unset($objReport);

		if ($format=='html') {
			$toolBarPrint=$this->getToolBarPrint($params);
			$result=<<<HTML
<div class="container">
{$toolBarPrint}
{$result}
</div>
HTML;
		}
		return $result;
	}
}
return new ReportDataModel();