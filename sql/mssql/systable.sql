create table sysfieldtype (
	id int primary key identity,
	name varchar(255) not null default '',
	isid int not null default 0,
	isref int not null default 0,
	isdec int not null default 0,
	isstr int not null default 0,
	isdat int not null default 0,
	defvalue varchar(255) not null default '',
	jstype varchar(255) not null default '',
	g740type varchar(255) not null default '',
	ord bigint not null default 0
);

create table systablecategory (
	id int primary key identity,
	name varchar(255) not null default '',
	ord bigint not null default 0
);

create table systable (
	id int primary key identity,
	klssystablecategory int not null default 0,
	tablename varchar(255) not null default '',
	name varchar(255) not null default '',
	orderby text not null default '',
	fields text not null default '',
	permmode varchar(255) not null default '',
	isstatic int not null default 0,
	isdynamic int not null default 0,
	issystem int not null default 0
);
create index idx_systable_klssystablecategory on systable (klssystablecategory);

create table sysfield (
	id int primary key identity,
	klssystable  int not null default 0,
	fieldname varchar(255) not null default '',
	name varchar(255) not null default '',
	klssysfieldtype  int not null default 0,
	isnotempty int not null default 0,
	ismain int not null default 0,
	maxlength int not null default 0,
	[len] int not null default 0,
	[dec] int not null default 0,
	isstretch int not null default 0,
	klsreftable  int not null default 0,
	reflink varchar(255) not null default '',
	isrefrestrict int not null default 0,
	isrefcascade int not null default 0,
	isrefclear int not null default 0,
	isref121 int not null default 0,
	ord bigint not null default 0
);
create index idx_sysfield_klssystable on sysfield (klssystable);
create index idx_sysfield_klssysfieldtype on sysfield (klssysfieldtype);

create table sysfieldparams (
	id int primary key identity,
	klssysfield int not null default 0,
	name varchar(255) not null default '',
	val text not null default ''
);
create index idx_sysfieldparams_klssysfield on sysfieldparams (klssysfield);

create table sysappmenu (
	id int primary key identity,
	parentid int not null default 0,
	name varchar(255) not null default '',
	description varchar(255) not null default '',
	form varchar(255) not null default '',
	icon varchar(255) not null default '',
	params text not null default '',
	permmode varchar(255) not null default '',
	permoper varchar(255) not null default '',
	ord bigint not null default 0
);
create index idx_sysappmenu_parentid on sysappmenu (parentid);

create table sysextlog(
	id int primary key identity,
	d datetime,
	tstart varchar(5) not null default '',
	tend varchar(5) not null default '',
	name varchar(64) not null default '',
	message text,
	iserror int not null default 0
);

create table sysdblog (
	id bigint primary key identity,
	parent varchar(36) not null default '',
	parentid varchar(36) not null default '',
	[table] varchar(36) not null default '',
	[field] varchar(36) not null default '',
	[rowid] varchar(36) not null default '',
	[operation] varchar(3) not null default '',
	[value] varchar(1024) not null default '',
	[child] varchar(36) not null default '',
	childid varchar(36) not null default '',
	[user] varchar(36) not null default '',
	d datetime,
	t varchar(8) not null default ''
);
create index idx_sysdblog_parent on sysdblog (parent, parentid, [table]);
create index idx_sysdblog_table on sysdblog ([table], [rowid]);
create index idx_sysdblog_d on sysdblog (d);


create table sysconfig (
	id int primary key identity,
	code varchar(32) not null default '',
	val varchar(64) not null default ''
);
create unique index idx_sysconfig_code on sysconfig (code);

truncate table [dbo].[sysfieldtype];
set identity_insert [dbo].[sysfieldtype] on;
insert into [sysfieldtype] ([id],[name],[isid],[isref],[isdec],[isstr],[isdat],[jstype],[g740type],[ord]) values ('1','Id','1','0','1','0','0','id','id','10');
insert into [sysfieldtype] ([id],[name],[isid],[isref],[isdec],[isstr],[isdat],[jstype],[g740type],[ord]) values ('2','Строка','0','0','0','1','0','edit','string','20');
insert into [sysfieldtype] ([id],[name],[isid],[isref],[isdec],[isstr],[isdat],[jstype],[g740type],[ord]) values ('3','Memo','0','0','0','1','0','memo','memo','30');
insert into [sysfieldtype] ([id],[name],[isid],[isref],[isdec],[isstr],[isdat],[defvalue],[jstype],[g740type],[ord]) values ('4','Число','0','0','1','0','0','0','num','num','40');
insert into [sysfieldtype] ([id],[name],[isid],[isref],[isdec],[isstr],[isdat],[defvalue],[jstype],[g740type],[ord]) values ('5','Дата','0','0','0','0','1','null','date','date','50');
insert into [sysfieldtype] ([id],[name],[isid],[isref],[isdec],[isstr],[isdat],[defvalue],[jstype],[g740type],[ord]) values ('6','Галочка','0','0','1','0','0','0','check','check','60');
insert into [sysfieldtype] ([id],[name],[isid],[isref],[isdec],[isstr],[isdat],[jstype],[g740type],[ord]) values ('7','Ссылка','0','1','0','0','0','ref','ref','70');
insert into [sysfieldtype] ([id],[name],[isid],[isref],[isdec],[isstr],[isdat],[jstype],[g740type],[ord]) values ('8','Список','0','0','0','0','0','list','list','80');
insert into [sysfieldtype] ([id],[name],[isid],[isref],[isdec],[isstr],[isdat],[jstype],[g740type],[ord]) values ('9','Иконки','0','0','0','0','0','icons','icons','90');
insert into [sysfieldtype] ([id],[name],[isid],[isref],[isdec],[isstr],[isdat],[jstype],[g740type],[ord]) values ('10','Радиогруппа','0','0','0','0','0','radio','radio','100');
insert into [sysfieldtype] ([id],[name],[isid],[isref],[isdec],[isstr],[isdat],[jstype],[g740type],[ord]) values ('11','Список множественный','0','0','0','0','0','reflist','reflist','110');
insert into [sysfieldtype] ([id],[name],[isid],[isref],[isdec],[isstr],[isdat],[jstype],[g740type],[ord]) values ('12','Дерево множественное','0','0','0','0','0','reftree','reftree','120');
set identity_insert [dbo].[sysfieldtype] off;

truncate table [dbo].[sysappmenu];
set identity_insert [dbo].[sysappmenu] on;
insert into [sysappmenu] ([id],[parentid],[name],[description],[ord]) values ('-99','-99','root','root','0');
insert into [sysappmenu] ([id],[parentid],[name],[icon],[permmode],[permoper],[ord]) values ('1','-99','Уголок разработчика','root','root','read','0');
insert into [sysappmenu] ([id],[parentid],[name],[form],[icon],[ord]) values ('2','1','Структура базы','formSysTreeDataModel','tree','0');
insert into [sysappmenu] ([id],[parentid],[name],[form],[icon],[ord]) values ('3','1','Главное меню системы','formSysTreeMenu','menu','10');
insert into [sysappmenu] ([id],[parentid],[name],[form],[icon],[ord]) values ('4','1','Типы полей базы данных','formSysFieldType','ref','20');
set identity_insert [dbo].[sysappmenu] off;

insert into sysconfig (code, val) values ('dbversion','0');