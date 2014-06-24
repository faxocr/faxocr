
bash "install static version of wkhtmltopdf" do
  cwd "/tmp"
  user "root"
  group "root"
  code <<-EOH
    wget -q http://downloads.sourceforge.net/project/wkhtmltopdf/0.12.0/#{node[:faxocr][:wkhtmltopdf][:archive]}
    tar xf #{node[:faxocr][:wkhtmltopdf][:archive]}
    install -m 755 -o root -g root wkhtmltox/bin/* #{node[:faxocr][:wkhtmltopdf][:install_path]}
    rm #{node[:faxocr][:wkhtmltopdf][:archive]}
    rm -r wkhtmltox
    EOH
  not_if { ::File.exists?("#{node[:faxocr][:wkhtmltopdf][:install_path]}/wkhtmltopdf") }
end

# vim:set expandtab shiftwidth=2 tabstop=2 softtabstop=2:
