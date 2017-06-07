create table sysmenu (
  id int not null auto_increment,
  klsparent int not null default '0',
  name varchar(255) not null default '',
  form varchar(255) not null default '',
  icon varchar(255) not null default '',
  params longtext not null,
  permmode varchar(255) not null default '',
  permoper varchar(255) not null default '',
  ord bigint not null default '0',
  primary key (id),
  key klsparent (klsparent)
);
insert into sysmenu (id, klsparent, name, form, icon, params, permmode, permoper, ord) values
(1, 0, 'Уголок разработчика', '', 'computer', '', 'sys', 'read', 0),
(2, 0, 'Выйти', 'formDisconnect', 'disconnect', '', 'connected', 'read', 67),
(3, 1, 'База данных', '', 'dbtable', '', 'sys', 'read', 100),
(4, 1, 'Главное меню системы', 'formSysMenu', 'chart', '', 'sys', 'read', 167),
(5, 3, 'Структура базы', 'formTreeDataModel', 'database', '', 'sys', 'read', 100),
(6, 3, 'Типы полей базы данных', 'formSysFieldType', 'table', '', 'sys', 'read', 167);


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
insert into sysfieldtype (id, name, isid, isref, isdec, isstr, isdat, defvalue, jstype, g740type, ord) values
(1, 'Строка', 0, 0, 0, 1, 0, '', 'edit', 'string', 10),
(2, 'Memo', 0, 0, 0, 1, 0, '', 'memo', 'memo', 20),
(3, 'Число', 0, 0, 1, 0, 0, '0', 'num', 'num', 30),
(4, 'Дата', 0, 0, 0, 0, 1, 'null', 'date', 'date', 40),
(6, 'Галочка', 0, 0, 0, 0, 0, '0', 'check', 'check', 60),
(7, 'Ссылка', 0, 1, 0, 0, 0, '0', 'ref', 'ref', 70),
(8, 'Id', 1, 0, 1, 0, 0, '0', 'id', 'id', 5),
(9, 'Список', 0, 0, 0, 0, 0, '', 'list', 'list', 137),
(10, 'Иконки', 0, 0, 0, 0, 0, '', 'icons', 'icons', 204),
(11, 'Радиогруппа', 0, 0, 0, 0, 0, '', 'radio', 'radio', 271),
(12, 'Список множественный', 0, 0, 0, 0, 0, '', 'reflist', 'reflist', 338),
(13, 'Дерево множественное', 0, 0, 0, 0, 0, '', 'reftree', 'reftree', 405);

create table systablecategory (
  id int not null auto_increment,
  name varchar(255) not null,
  ord bigint not null default '0',
  primary key (id)
);
insert into systablecategory (id, name, ord) values
(1, 'Структура базы', 0),
(2, 'Системное', 0);

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
insert into systable (id, klssystablecategory, tablename, name, isstatic, isdynamic, issystem, orderby, permmode, fields) values
(1, 1, 'sysfield', 'Поле таблицы', 0, 0, 1, 'sysfield.klssystable, sysfield.ord, sysfield.id', 'sys', ''),
(2, 1, 'sysfieldparams', 'Параметр поля', 0, 0, 1, 'sysfieldparams.klssysfield, sysfieldparams.name', 'sys', ''),
(3, 1, 'sysfieldtype', 'Тип поля', 0, 0, 1, 'sysfieldtype.ord, sysfieldtype.id', 'sys', ''),
(4, 1, 'systable', 'Таблица', 0, 0, 1, 'systable.klssystablecategory, systable.tablename, systable.id', 'sys', ''),
(5, 1, 'systablecategory', 'Категория таблицы', 0, 0, 1, 'systablecategory.ord, systablecategory.id', 'sys', ''),
(6, 2, 'sysmenu', 'Главное меню системы', 0, 0, 1, 'sysmenu.klsparent, sysmenu.ord, sysmenu.id', 'sysref', 'case when exists(select * from sysmenu child where child.klsparent=sysmenu.id)  then 0 else 1 end as row_empty,\n"menuitem" as row_type,\nsysmenu.icon as row_icon\n');


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

insert into sysfield (id, klssystable, fieldname, name, isnotempty, klssysfieldtype, maxlength, len, `dec`, klsreftable, reflink, isrefrestrict, isrefcascade, isrefclear, isref121, ord, ismain, isstretch) values
(1, 5, 'id', 'Id', 1, 8, 0, 0, 0, 0, '', 0, 0, 0, 0, 0, 0, 0),
(2, 5, 'name', 'Категория', 1, 1, 255, 12, 0, 0, '', 0, 0, 0, 0, 67, 1, 1),
(3, 5, 'ord', '№пп', 0, 3, 0, 5, 0, 0, '', 0, 0, 0, 0, 134, 0, 0),
(4, 4, 'id', 'Id', 1, 8, 0, 0, 0, 0, '', 0, 0, 0, 0, 0, 0, 0),
(5, 4, 'tablename', 'Таблица', 1, 1, 255, 12, 0, 0, '', 0, 0, 0, 0, 67, 1, 0),
(6, 4, 'name', 'Описание', 0, 1, 255, 25, 0, 0, '', 0, 0, 0, 0, 134, 1, 0),
(7, 4, 'klssystablecategory', 'Ссылка на категорию таблицы', 1, 7, 0, 0, 0, 5, '', 1, 0, 0, 0, 201, 0, 0),
(8, 4, 'isstatic', 'Статичная таблица', 0, 6, 0, 4, 0, 0, '', 0, 0, 0, 0, 268, 0, 0),
(9, 4, 'isdynamic', 'Динамичная таблица', 0, 6, 0, 4, 0, 0, '', 0, 0, 0, 0, 335, 0, 0),
(10, 4, 'issystem', 'Системная таблица', 0, 6, 0, 4, 0, 0, '', 0, 0, 0, 0, 402, 0, 0),
(11, 4, 'orderby', 'Сортировка', 0, 2, 0, 0, 0, 0, '', 0, 0, 0, 0, 469, 0, 0),
(12, 4, 'fields', 'Дополнительные поля', 0, 2, 0, 0, 0, 0, '', 0, 0, 0, 0, 536, 0, 0),
(13, 4, 'permmode', 'Режим по правам', 0, 1, 255, 12, 0, 0, '', 0, 0, 0, 0, 603, 0, 0),
(14, 3, 'id', 'Id', 1, 8, 0, 0, 0, 0, '', 0, 0, 0, 0, 0, 0, 0),
(15, 3, 'name', 'Тип', 1, 1, 255, 12, 0, 0, '', 0, 0, 0, 0, 67, 1, 0),
(16, 3, 'isid', 'id', 0, 6, 0, 5, 0, 0, '', 0, 0, 0, 0, 134, 0, 0),
(17, 3, 'isref', 'Ссылка', 0, 6, 0, 6, 0, 0, '', 0, 0, 0, 0, 201, 0, 0),
(18, 3, 'isdec', 'Число', 0, 6, 0, 5, 0, 0, '', 0, 0, 0, 0, 268, 0, 0),
(19, 3, 'isstr', 'Строка', 0, 6, 0, 6, 0, 0, '', 0, 0, 0, 0, 335, 0, 0),
(20, 3, 'isdat', 'Дата', 0, 6, 0, 5, 0, 0, '', 0, 0, 0, 0, 402, 0, 0),
(21, 3, 'defvalue', 'Значение по умолчанию', 0, 1, 255, 15, 0, 0, '', 0, 0, 0, 0, 469, 0, 0),
(22, 3, 'jstype', 'Тип в JavaScript', 0, 1, 255, 15, 0, 0, '', 0, 0, 0, 0, 536, 0, 0),
(23, 3, 'g740type', 'Тип в g740', 0, 1, 255, 15, 0, 0, '', 0, 0, 0, 0, 603, 1, 0),
(24, 3, 'ord', '№пп', 0, 3, 0, 5, 0, 0, '', 0, 0, 0, 0, 670, 0, 0),
(25, 1, 'id', 'Id', 1, 8, 0, 0, 0, 0, '', 0, 0, 0, 0, 0, 0, 0),
(26, 1, 'klssystable', 'Ссылка на родительскую таблицу', 1, 7, 0, 0, 0, 4, '', 0, 1, 0, 0, 67, 0, 0),
(27, 1, 'fieldname', 'Поле', 1, 1, 255, 15, 0, 0, '', 0, 0, 0, 0, 134, 1, 0),
(28, 1, 'name', 'Описание поля', 0, 1, 255, 25, 0, 0, '', 0, 0, 0, 0, 201, 1, 1),
(29, 1, 'isnotempty', 'Не пусто', 0, 6, 0, 5, 0, 0, '', 0, 0, 0, 0, 268, 0, 0),
(30, 1, 'ismain', 'Main', 0, 6, 0, 5, 0, 0, '', 0, 0, 0, 0, 335, 0, 0),
(31, 1, 'isstretch', 'Stretch', 0, 6, 0, 5, 0, 0, '', 0, 0, 0, 0, 402, 0, 0),
(32, 1, 'klssysfieldtype', 'Ссылка на тип поля', 0, 7, 0, 0, 0, 3, '', 1, 0, 0, 0, 469, 1, 0),
(33, 1, 'maxlength', 'Максимальная длина', 0, 3, 0, 5, 0, 0, '', 0, 0, 0, 0, 536, 0, 0),
(34, 1, 'len', 'Длина', 0, 3, 0, 5, 0, 0, '', 0, 0, 0, 0, 603, 0, 0),
(35, 1, 'dec', 'После запятой', 0, 3, 0, 5, 0, 0, '', 0, 0, 0, 0, 670, 0, 0),
(36, 1, 'klsreftable', 'Ссылка на связанную таблицу', 0, 7, 0, 0, 0, 4, 'reftable', 1, 0, 0, 0, 737, 0, 0),
(37, 1, 'reflink', 'Имя ссылки', 0, 1, 255, 15, 0, 0, '', 0, 0, 0, 0, 804, 0, 0),
(38, 1, 'isrefrestrict', 'Restrict связь', 0, 6, 0, 5, 0, 0, '', 0, 0, 0, 0, 871, 0, 0),
(39, 1, 'isrefcascade', 'Cascade связь', 0, 6, 0, 5, 0, 0, '', 0, 0, 0, 0, 938, 0, 0),
(40, 1, 'isrefclear', 'Clear связь', 0, 6, 0, 5, 0, 0, '', 0, 0, 0, 0, 1005, 0, 0),
(41, 1, 'isref121', '1 к 1 связь', 0, 6, 0, 5, 0, 0, '', 0, 0, 0, 0, 1072, 0, 0),
(42, 1, 'ord', '№пп', 0, 3, 0, 5, 0, 0, '', 0, 0, 0, 0, 1139, 0, 0),
(43, 2, 'id', 'Id', 1, 8, 0, 0, 0, 0, '', 0, 0, 0, 0, 0, 0, 0),
(44, 2, 'klssysfield', 'Ссылка на поле', 1, 7, 0, 0, 0, 1, '', 0, 1, 0, 0, 67, 0, 0),
(45, 2, 'name', 'Параметр', 1, 1, 255, 15, 0, 0, '', 0, 0, 0, 0, 134, 0, 0),
(46, 2, 'val', 'Значение', 0, 1, 255, 65, 0, 0, '', 0, 0, 0, 0, 201, 0, 0),
(47, 6, 'id', 'Id', 1, 8, 0, 0, 0, 0, '', 0, 0, 0, 0, 0, 0, 0),
(48, 6, 'klsparent', 'Ссылка на родителя', 0, 7, 0, 0, 0, 6, '', 0, 1, 0, 0, 67, 0, 0),
(49, 6, 'name', 'Пункт меню', 1, 1, 255, 25, 0, 0, '', 0, 0, 0, 0, 134, 1, 0),
(50, 6, 'form', 'Экранная форма', 0, 1, 255, 15, 0, 0, '', 0, 0, 0, 0, 201, 0, 0),
(51, 6, 'icon', 'Иконка', 0, 1, 255, 10, 0, 0, '', 0, 0, 0, 0, 268, 0, 0),
(52, 6, 'params', 'Параметры вызова', 0, 2, 0, 0, 0, 0, '', 0, 0, 0, 0, 335, 0, 1),
(53, 6, 'permmode', 'Права, режим', 0, 1, 255, 10, 0, 0, '', 0, 0, 0, 0, 402, 0, 1),
(54, 6, 'permoper', 'Права, операция', 0, 1, 255, 10, 0, 0, '', 0, 0, 0, 0, 469, 0, 1),
(55, 6, 'ord', '№пп', 0, 3, 0, 5, 0, 0, '', 0, 0, 0, 0, 536, 0, 0);

create table sysfieldparams (
  id int not null auto_increment,
  klssysfield int not null default '0',
  name varchar(255) not null,
  val varchar(255) not null,
  primary key (id),
  key klssysfield (klssysfield)
);
