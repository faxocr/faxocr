
node[:faxocr][:wkhtmltopdf][:dependentPackages].each do |pkg|
  package pkg do
    action :install
  end
end

remote_file "/tmp/#{node[:faxocr][:wkhtmltopdf][:archive]}" do
  source node[:faxocr][:wkhtmltopdf][:url]
  not_if { ::File.exists?("/tmp/#{node[:faxocr][:wkhtmltopdf][:archive]}") }
end

dpkg_package node[:faxocr][:wkhtmltopdf][:archive] do
  source "/tmp/#{node[:faxocr][:wkhtmltopdf][:archive]}"
  action :install
end

# vim:set expandtab shiftwidth=2 tabstop=2 softtabstop=2:
