
default[:faxocr][:apache_conf][:type]   = "normal" # or virtual


if node[:faxocr][:apache_conf][:type] == "normal"
  default[:faxocr][:apache_conf][:conffile] = "apache-faxocr.conf.erb"
elsif node[:faxocr][:apache_conf][:type] == "virtual"
  default[:faxocr][:apache_conf][:conffile] = "apache-faxocr-virtual.conf.erb"
else
end

# vim:set expandtab shiftwidth=2 tabstop=2 softtabstop=2:
