
template "/etc/mysql/conf.d/faxocr.cnf" do
  source "faxocr.cnf.erb"
  owner "root"
  group "root"
  mode 0644
end

bash "setup faxocr DB for ruby on rails" do
  code <<-EOH
    echo ' \
      drop database if exists faxocr_development; \
      drop database if exists faxocr_test; \
      drop database if exists faxocr_production; \
      create database faxocr_development; \
      create database faxocr_test; \
      create database faxocr_production; \
 \
      delete from user where user="faxocr"; \
      insert into user set user="faxocr", password=password("faxocr"), host="localhost"; \
      flush privileges; \
 \
      grant all on faxocr_development.* to faxocr@localhost; \
      grant all on faxocr_production.* to faxocr@localhost; \
      grant all on faxocr_test.* to faxocr@localhost; \
    ' | mysql -u root --password=#{node[:mysql][:server_root_password]} mysql
    EOH
  only_if { node[:faxocr][:setup_mode] == "initial_setup" }
end

# vim:set expandtab shiftwidth=2 tabstop=2 softtabstop=2:
