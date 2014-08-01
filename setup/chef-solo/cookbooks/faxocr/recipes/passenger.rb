
gem_package "passenger" do
  gem_binary "gem1.8"
  version node[:faxocr][:passenger_version]
end

%w{ libcurl4-openssl-dev apache2-threaded-dev libapr1-dev libaprutil1-dev
}.each do |pkg|
  package pkg do
    action :install
  end
end

execute "passenger_module" do
  command 'passenger-install-apache2-module --auto'
  creates node[:faxocr][:passenger_module_path]
  not_if { ::File.exists?(node[:faxocr][:passenger_module_path]) }
end

template "#{node[:apache][:dir]}/mods-available/passenger.load" do
  source 'passenger.load.erb'
  owner 'root'
  group 'root'
  mode 0755
  only_if { platform_family?('debian') }
end

template "#{node[:apache][:dir]}/mods-available/passenger.conf" do
  source 'passenger.conf.erb'
  owner 'root'
  group 'root'
  mode 0644
end

apache_module 'passenger' do
  module_path node[:passenger_module_path]
end

# vim:set expandtab shiftwidth=2 tabstop=2 softtabstop=2:
