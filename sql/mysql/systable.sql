create table sysfieldtype (
  id int not null auto_increment,
  name varchar(255) not null,
  isid int not null default '0',
  isref int not null default '0',
  isdec int not null default '0',
  isstr int not null default '0',
  isdat int not null default '0',
  defvalue varchar(255) not null,
  jstype varchar(255) not null,
  g740type varchar(255) not null default '',
  ord bigint not null default '0',
  primary key (id)
);

create table systablecategory (
  id int not null auto_increment,
  name varchar(255) not null,
  ord bigint not null default '0',
  primary key (id)
);

create table systable (
  id int not null auto_increment,
  klssystablecategory int not null default '0',
  tablename varchar(255) not null,
  name varchar(255) not null,
  isstatic int not null default '0',
  isdynamic int not null default '0',
  issystem int not null default '0',
  orderby longtext not null,
  permmode varchar(255) not null default '',
  fields longtext not null,
  primary key (id)
);

create table sysfield (
  id int not null auto_increment,
  klssystable int not null default '0',
  fieldname varchar(255) not null,
  name varchar(255) not null,
  isnotempty int not null default '0',
  klssysfieldtype int not null default '0',
  maxlength int not null default '0',
  len int not null default '0',
  `dec` int not null default '0',
  klsreftable int not null default '0',
  reflink varchar(255) not null default '',
  isrefrestrict int not null default '0',
  isrefcascade int not null default '0',
  isrefclear int not null default '0',
  isref121 int not null default '0',
  ord bigint not null default '0',
  ismain int not null default '0',
  isstretch int not null default '0',
  primary key (id),
  key klssystable (klssystable),
  key klsreftable (klsreftable)
);

create table sysfieldparams (
  id int not null auto_increment,
  klssysfield int not null default '0',
  name varchar(255) not null,
  val varchar(255) not null,
  primary key (id),
  key klssysfield (klssysfield)
);

create table sysappmenu (
  id int not null auto_increment,
  parentid int not null default '0',
  name varchar(255) not null default '',
  description longtext not null,
  form varchar(255) not null default '',
  icon varchar(255) not null default '',
  params longtext not null,
  permmode varchar(255) not null default '',
  permoper varchar(255) not null default '',
  ord bigint not null default '0',
  primary key (id),
  key parentid (parentid)
);

create table sysextlog(
  id int not null auto_increment,
  d date,
  tstart varchar(5) not null default '',
  tend varchar(5) not null default '',
  name varchar(64) not null default '',
  message longtext,
  iserror int not null default '0',
  primary key (id)
);

create table sysconfig (
	id int not null auto_increment,
	code varchar(32) not null default '',
	val varchar(64) not null default '',
	primary key (id),
	key code (code)
);

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

insert into `sysappmenu` (`id`,`parentid`,`name`,`description`,`form`,`icon`,`params`,`permmode`,`permoper`,`ord`) values ('-99','-99','root','root','','','','','','0');
insert into `sysappmenu` (`id`,`parentid`,`name`,`description`,`form`,`icon`,`params`,`permmode`,`permoper`,`ord`) values ('1','-99','Уголок разработчика','','','root','','root','read','0');
insert into `sysappmenu` (`id`,`parentid`,`name`,`description`,`form`,`icon`,`params`,`permmode`,`permoper`,`ord`) values ('4','1','Структура базы','','formSysTreeDataModel','tree','','','','0');
insert into `sysappmenu` (`id`,`parentid`,`name`,`description`,`form`,`icon`,`params`,`permmode`,`permoper`,`ord`) values ('5','1','Главное меню системы','','formSysTreeMenu','menu','','','','10');
insert into `sysappmenu` (`id`,`parentid`,`name`,`description`,`form`,`icon`,`params`,`permmode`,`permoper`,`ord`) values ('6','1','Типы полей базы данных','','formSysFieldType','ref','','','','20');

insert into sysconfig (code, val) values ('dbversion','0');