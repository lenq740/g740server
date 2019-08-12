create table "sysfieldtype" (
	"id" serial primary key,
	"name" varchar(255) not null default '',
	"isid" boolean not null default '0',
	"isref" boolean not null default '0',
	"isdec" boolean not null default '0',
	"isstr" boolean not null default '0',
	"isdat" boolean not null default '0',
	"defvalue" varchar(255) not null default '',
	"jstype" varchar(255) not null default '',
	"g740type" varchar(255) not null default '',
	"ord" bigint not null default 0
);

create table "systablecategory" (
	"id" serial primary key,
	"name" varchar(255) not null default '',
	"ord" bigint not null default 0
);

create table "systable" (
	"id" serial primary key,
	"klssystablecategory" integer references "systablecategory" on delete cascade deferrable initially deferred,
	"tablename" varchar(255) not null default '',
	"name" varchar(255) not null default '',
	"orderby" text not null default '',
	"fields" text not null default '',
	"permmode" varchar(255) not null default '',
	"isstatic" boolean not null default '0',
	"isdynamic" boolean not null default '0',
	"issystem" boolean not null default '0'
);

create table "sysfield" (
	"id" serial primary key,
	"klssystable" integer references "systable" on delete cascade deferrable initially deferred,
	"fieldname" varchar(255) not null default '',
	"name" varchar(255) not null default '',
	"klssysfieldtype" integer references "sysfieldtype" on delete restrict deferrable initially deferred,
	"isnotempty" boolean not null default '0',
	"ismain" boolean not null default '0',
	"maxlength" integer not null default 0,
	"len" integer not null default 0,
	"dec" integer not null default 0,
	"isstretch" boolean not null default '0',
	"klsreftable" integer references "systable" on delete restrict deferrable initially deferred,
	"reflink" varchar(255) not null default '',
	"isrefrestrict" boolean not null default '0',
	"isrefcascade" boolean not null default '0',
	"isrefclear" boolean not null default '0',
	"isref121" boolean not null default '0',
	"ord" bigint not null default 0
);


create table "sysfieldparams" (
	"id" serial primary key,
	"klssysfield" integer references "sysfield" on delete cascade deferrable initially deferred,
	"name" varchar(255) not null default '',
	"val" text not null default ''
);

create table "sysappmenu" (
	"id" serial primary key,
	"parentid" integer,
	"name" varchar(255) not null default '',
	"description" varchar(255) not null default '',
	"form" varchar(255) not null default '',
	"icon" varchar(255) not null default '',
	"params" text,
	"permmode" varchar(255) not null default '',
	"permoper" varchar(255) not null default '',
	"ord" bigint not null default 0
);
create index "idx_sysappmenu_parentid" on "sysappmenu" ("parentid");

create table "sysextlog" (
	"id" serial primary key,
	"d" date,
	"tstart" varchar(5) not null default '',
	"tend" varchar(5) not null default '',
	"name" varchar(64) not null default '',
	"message" text,
	"iserror" int not null default '0'
);

create table "sysconfig" (
	"id" serial primary key,
	"code" varchar(32) not null default '',
	"val" varchar(64) not null default ''
);
alter table "sysconfig" add unique("code") deferrable initially deferred;

insert into "sysfieldtype" ("id","name","isid","isref","isdec","isstr","isdat","defvalue","jstype","g740type","ord") values ('1','Id','1','0','1','0','0','','id','id','10');
insert into "sysfieldtype" ("id","name","isid","isref","isdec","isstr","isdat","defvalue","jstype","g740type","ord") values ('2','Строка','0','0','0','1','0','','edit','string','20');
insert into "sysfieldtype" ("id","name","isid","isref","isdec","isstr","isdat","defvalue","jstype","g740type","ord") values ('3','Memo','0','0','0','1','0','','memo','memo','30');
insert into "sysfieldtype" ("id","name","isid","isref","isdec","isstr","isdat","defvalue","jstype","g740type","ord") values ('4','Число','0','0','1','0','0','0','num','num','40');
insert into "sysfieldtype" ("id","name","isid","isref","isdec","isstr","isdat","defvalue","jstype","g740type","ord") values ('5','Дата','0','0','0','0','1','null','date','date','50');
insert into "sysfieldtype" ("id","name","isid","isref","isdec","isstr","isdat","defvalue","jstype","g740type","ord") values ('6','Галочка','0','0','1','0','0','0','check','check','60');
insert into "sysfieldtype" ("id","name","isid","isref","isdec","isstr","isdat","defvalue","jstype","g740type","ord") values ('7','Ссылка','0','1','0','0','0','','ref','ref','70');
insert into "sysfieldtype" ("id","name","isid","isref","isdec","isstr","isdat","defvalue","jstype","g740type","ord") values ('8','Список','0','0','0','0','0','','list','list','80');
insert into "sysfieldtype" ("id","name","isid","isref","isdec","isstr","isdat","defvalue","jstype","g740type","ord") values ('9','Иконки','0','0','0','0','0','','icons','icons','90');
insert into "sysfieldtype" ("id","name","isid","isref","isdec","isstr","isdat","defvalue","jstype","g740type","ord") values ('10','Радиогруппа','0','0','0','0','0','','radio','radio','100');
insert into "sysfieldtype" ("id","name","isid","isref","isdec","isstr","isdat","defvalue","jstype","g740type","ord") values ('11','Список множественный','0','0','0','0','0','','reflist','reflist','110');
insert into "sysfieldtype" ("id","name","isid","isref","isdec","isstr","isdat","defvalue","jstype","g740type","ord") values ('12','Дерево множественное','0','0','0','0','0','','reftree','reftree','120');
select setval('sysfieldtype_id_seq', GREATEST(1,(select max(id) from "sysfieldtype")));

insert into "sysappmenu" ("id","parentid","name","description","form","icon","params","permmode","permoper","ord") values ('-99','-99','root','root','','',null,'','','0');
insert into "sysappmenu" ("id","parentid","name","description","form","icon","params","permmode","permoper","ord") values ('1','-99','Уголок разработчика','','','root','','root','read','0');
insert into "sysappmenu" ("id","parentid","name","description","form","icon","params","permmode","permoper","ord") values ('2','1','Структура базы','','formSysTreeDataModel','tree','','','','0');
insert into "sysappmenu" ("id","parentid","name","description","form","icon","params","permmode","permoper","ord") values ('3','1','Главное меню системы','','formSysTreeMenu','menu','','','','10');
insert into "sysappmenu" ("id","parentid","name","description","form","icon","params","permmode","permoper","ord") values ('4','1','Типы полей базы данных','','formSysFieldType','ref','','','','20');
select setval('sysappmenu_id_seq', GREATEST(1,(select max(id) from "sysappmenu")));

insert into "sysconfig" ("code", "val") values ('dbversion','0');
