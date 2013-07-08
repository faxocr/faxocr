create database if not exists faxocr_development;
create database if not exists faxocr_test;
create database if not exists faxocr_production;

use mysql;
insert into user set user="faxocr", password=password("faxocr"), host="localhost";
flush privileges;

grant all on faxocr_development.* to faxocr@localhost;
grant all on faxocr_production.* to faxocr@localhost;
grant all on faxocr_test.* to faxocr@localhost;
