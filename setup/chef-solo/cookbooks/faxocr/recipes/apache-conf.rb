
template "/etc/apache2/sites-available/faxocr.conf" do
  source "apache-faxocr.conf.erb"
  owner "root"
  group node[:apache][:root_group]
  mode 00644
  only_if { platform_family?("debian") }
end

link "/etc/apache2/sites-enabled/faxocr.conf" do
  to "/etc/apache2/sites-available/faxocr.conf"
  link_type :symbolic
  notifies :restart, "service[apache2]"
  only_if { platform_family?("debian") }
end

# vim:set expandtab shiftwidth=2 tabstop=2 softtabstop=2:
