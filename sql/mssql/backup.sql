
truncate table [dbo].[systablecategory];
set identity_insert [dbo].[systablecategory] on;
set identity_insert [dbo].[systablecategory] off;


truncate table [dbo].[systable];
set identity_insert [dbo].[systable] on;
set identity_insert [dbo].[systable] off;


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


truncate table [dbo].[sysfield];
set identity_insert [dbo].[sysfield] on;
set identity_insert [dbo].[sysfield] off;


truncate table [dbo].[sysfieldparams];
set identity_insert [dbo].[sysfieldparams] on;
set identity_insert [dbo].[sysfieldparams] off;


truncate table [dbo].[sysappmenu];
set identity_insert [dbo].[sysappmenu] on;
insert into [sysappmenu] ([id],[parentid],[name],[description],[ord]) values ('-99','-99','root','root','0');
insert into [sysappmenu] ([id],[parentid],[name],[icon],[permmode],[permoper],[ord]) values ('1','-99','Уголок разработчика','root','root','read','0');
insert into [sysappmenu] ([id],[parentid],[name],[form],[icon],[ord]) values ('2','1','Структура базы','formSysTreeDataModel','tree','0');
insert into [sysappmenu] ([id],[parentid],[name],[form],[icon],[ord]) values ('3','1','Главное меню системы','formSysTreeMenu','menu','10');
insert into [sysappmenu] ([id],[parentid],[name],[form],[icon],[ord]) values ('4','1','Типы полей базы данных','formSysFieldType','ref','20');
set identity_insert [dbo].[sysappmenu] off;

