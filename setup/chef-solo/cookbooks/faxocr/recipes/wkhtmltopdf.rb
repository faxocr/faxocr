
bash "install static version of wkhtmltopdf" do
  cwd "/tmp"
  user "root"
  group "root"
  code <<-EOH
    wget -q https://wkhtmltopdf.googlecode.com/files/#{node[:faxocr][:wkhtmltopdf][:archive]}
    tar xf #{node[:faxocr][:wkhtmltopdf][:archive]}
    install -m 755 -o root -g root #{node[:faxocr][:wkhtmltopdf][:binary_filename]} #{node[:faxocr][:wkhtmltopdf][:install_path]}/wkhtmltopdf
    rm  #{node[:faxocr][:wkhtmltopdf][:archive]}
    EOH
  not_if { ::File.exists?("#{node[:faxocr][:wkhtmltopdf][:install_path]}/wkhtmltopdf") }
end

# vim:set expandtab shiftwidth=2 tabstop=2 softtabstop=2:
