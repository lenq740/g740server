<?php
// источник данных sysmenu
class DataSource2_Sysmenu extends DataSource_Sysmenu {
	public function execRefresh($params=Array()) {
		$result=parent::execRefresh($params);
		if ($params['mode.treemenu']) {
			$lst=$result;
			$result=Array();
			foreach($lst as $key=>$rec) {
				if ($rec['permmode'] || $rec['permoper']) {
					if (!getPerm($rec['permmode'], $rec['permoper'])) continue;
				}
				$result[]=$rec;
			}
		}
		return $result;
	}
}
return new DataSource2_Sysmenu();
?>