
SET CONSTRAINTS ALL DEFERRED;
SET session_replication_role = replica;
delete from "systablecategory";

select setval('systablecategory_id_seq', GREATEST(1,(select max(id) from "systablecategory")));


SET CONSTRAINTS ALL DEFERRED;
SET session_replication_role = replica;
delete from "systable";

select setval('systable_id_seq', GREATEST(1,(select max(id) from "systable")));


SET CONSTRAINTS ALL DEFERRED;
SET session_replication_role = replica;
delete from "sysfieldtype";
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


SET CONSTRAINTS ALL DEFERRED;
SET session_replication_role = replica;
delete from "sysfield";

select setval('sysfield_id_seq', GREATEST(1,(select max(id) from "sysfield")));


SET CONSTRAINTS ALL DEFERRED;
SET session_replication_role = replica;
delete from "sysfieldparams";

select setval('sysfieldparams_id_seq', GREATEST(1,(select max(id) from "sysfieldparams")));


SET CONSTRAINTS ALL DEFERRED;
SET session_replication_role = replica;
delete from "sysappmenu";
insert into "sysappmenu" ("id","parentid","name","description","form","icon","params","permmode","permoper","ord") values ('-99','-99','root','root','','',null,'','','0');
insert into "sysappmenu" ("id","parentid","name","description","form","icon","params","permmode","permoper","ord") values ('1','-99','Уголок разработчика','','','root',null,'root','read','0');
insert into "sysappmenu" ("id","parentid","name","description","form","icon","params","permmode","permoper","ord") values ('2','1','Структура базы','','formSysTreeDataModel','tree',null,'','','0');
insert into "sysappmenu" ("id","parentid","name","description","form","icon","params","permmode","permoper","ord") values ('3','1','Главное меню системы','','formSysTreeMenu','menu',null,'','','10');
insert into "sysappmenu" ("id","parentid","name","description","form","icon","params","permmode","permoper","ord") values ('4','1','Типы полей базы данных','','formSysFieldType','ref',null,'','','20');

select setval('sysappmenu_id_seq', GREATEST(1,(select max(id) from "sysappmenu")));

