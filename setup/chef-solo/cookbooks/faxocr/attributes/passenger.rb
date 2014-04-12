
default[:faxocr][:passenger_version]   = "4.0.41"
default[:faxocr][:passenger_root_path]   = "/usr/lib/ruby/gems/1.8/gems/passenger-#{node[:faxocr][:passenger_version]}"
default[:faxocr][:passenger_module_path] = "/usr/lib/ruby/gems/1.8/gems/passenger-#{node[:faxocr][:passenger_version]}/buildout/apache2/mod_passenger.so"
default[:faxocr][:passenger_ruby_bin] = "/usr/bin/ruby1.8"

# vim:set expandtab shiftwidth=2 tabstop=2 softtabstop=2:
