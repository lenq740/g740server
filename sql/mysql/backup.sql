
lock table `systablecategory` write;
alter table `systablecategory` disable keys;
truncate table `systablecategory`;
alter table `systablecategory` enable keys;
unlock tables;
check table systablecategory;
optimize table systablecategory;
alter table `systablecategory` AUTO_INCREMENT=0;


lock table `systable` write;
alter table `systable` disable keys;
truncate table `systable`;
alter table `systable` enable keys;
unlock tables;
check table systable;
optimize table systable;
alter table `systable` AUTO_INCREMENT=0;


lock table `sysfieldtype` write;
alter table `sysfieldtype` disable keys;
truncate table `sysfieldtype`;
insert into `sysfieldtype` (`id`,`name`,`isid`,`isref`,`isdec`,`isstr`,`isdat`,`defvalue`,`jstype`,`g740type`,`ord`) values ('1','Id','1','0','1','0','0','','id','id','10');
insert into `sysfieldtype` (`id`,`name`,`isid`,`isref`,`isdec`,`isstr`,`isdat`,`defvalue`,`jstype`,`g740type`,`ord`) values ('2','Строка','0','0','0','1','0','','edit','string','20');
insert into `sysfieldtype` (`id`,`name`,`isid`,`isref`,`isdec`,`isstr`,`isdat`,`defvalue`,`jstype`,`g740type`,`ord`) values ('3','Memo','0','0','0','1','0','','memo','memo','30');
insert into `sysfieldtype` (`id`,`name`,`isid`,`isref`,`isdec`,`isstr`,`isdat`,`defvalue`,`jstype`,`g740type`,`ord`) values ('4','Число','0','0','1','0','0','0','num','num','40');
insert into `sysfieldtype` (`id`,`name`,`isid`,`isref`,`isdec`,`isstr`,`isdat`,`defvalue`,`jstype`,`g740type`,`ord`) values ('5','Дата','0','0','0','0','1','null','date','date','50');
insert into `sysfieldtype` (`id`,`name`,`isid`,`isref`,`isdec`,`isstr`,`isdat`,`defvalue`,`jstype`,`g740type`,`ord`) values ('6','Галочка','0','0','1','0','0','0','check','check','60');
insert into `sysfieldtype` (`id`,`name`,`isid`,`isref`,`isdec`,`isstr`,`isdat`,`defvalue`,`jstype`,`g740type`,`ord`) values ('7','Ссылка','0','1','0','0','0','','ref','ref','70');
insert into `sysfieldtype` (`id`,`name`,`isid`,`isref`,`isdec`,`isstr`,`isdat`,`defvalue`,`jstype`,`g740type`,`ord`) values ('8','Список','0','0','0','0','0','','list','list','80');
insert into `sysfieldtype` (`id`,`name`,`isid`,`isref`,`isdec`,`isstr`,`isdat`,`defvalue`,`jstype`,`g740type`,`ord`) values ('9','Иконки','0','0','0','0','0','','icons','icons','90');
insert into `sysfieldtype` (`id`,`name`,`isid`,`isref`,`isdec`,`isstr`,`isdat`,`defvalue`,`jstype`,`g740type`,`ord`) values ('10','Радиогруппа','0','0','0','0','0','','radio','radio','100');
insert into `sysfieldtype` (`id`,`name`,`isid`,`isref`,`isdec`,`isstr`,`isdat`,`defvalue`,`jstype`,`g740type`,`ord`) values ('11','Список множественный','0','0','0','0','0','','reflist','reflist','110');
insert into `sysfieldtype` (`id`,`name`,`isid`,`isref`,`isdec`,`isstr`,`isdat`,`defvalue`,`jstype`,`g740type`,`ord`) values ('12','Дерево множественное','0','0','0','0','0','','reftree','reftree','120');
alter table `sysfieldtype` enable keys;
unlock tables;
check table sysfieldtype;
optimize table sysfieldtype;
alter table `sysfieldtype` AUTO_INCREMENT=12;


lock table `sysfield` write;
alter table `sysfield` disable keys;
truncate table `sysfield`;
alter table `sysfield` enable keys;
unlock tables;
check table sysfield;
optimize table sysfield;
alter table `sysfield` AUTO_INCREMENT=0;


lock table `sysfieldparams` write;
alter table `sysfieldparams` disable keys;
truncate table `sysfieldparams`;
alter table `sysfieldparams` enable keys;
unlock tables;
check table sysfieldparams;
optimize table sysfieldparams;
alter table `sysfieldparams` AUTO_INCREMENT=0;


lock table `sysappmenu` write;
alter table `sysappmenu` disable keys;
truncate table `sysappmenu`;
insert into `sysappmenu` (`id`,`parentid`,`name`,`description`,`form`,`icon`,`params`,`permmode`,`permoper`,`ord`) values ('-99','-99','root','root','','','','','','0');
insert into `sysappmenu` (`id`,`parentid`,`name`,`description`,`form`,`icon`,`params`,`permmode`,`permoper`,`ord`) values ('1','-99','Уголок разработчика','','','root','','root','read','0');
insert into `sysappmenu` (`id`,`parentid`,`name`,`description`,`form`,`icon`,`params`,`permmode`,`permoper`,`ord`) values ('4','1','Структура базы','','formSysTreeDataModel','tree','','','','0');
insert into `sysappmenu` (`id`,`parentid`,`name`,`description`,`form`,`icon`,`params`,`permmode`,`permoper`,`ord`) values ('5','1','Главное меню системы','','formSysTreeMenu','menu','','','','10');
insert into `sysappmenu` (`id`,`parentid`,`name`,`description`,`form`,`icon`,`params`,`permmode`,`permoper`,`ord`) values ('6','1','Типы полей базы данных','','formSysFieldType','ref','','','','20');
alter table `sysappmenu` enable keys;
unlock tables;
check table sysappmenu;
optimize table sysappmenu;
alter table `sysappmenu` AUTO_INCREMENT=6;

