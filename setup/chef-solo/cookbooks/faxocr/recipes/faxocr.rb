
git "cloning faxocr" do
  repository "https://github.com/faxocr/faxocr"
  destination "/home/faxocr"
  action :sync
  user "root"
  group "root"
end

bash "change the owner of faxocr's home directory to faxocr" do
  user "root"
  group "root"
  code <<-EOH
    chown -R faxocr:faxocr #{node[:faxocr][:home_dir]}
    EOH
end

directory node[:faxocr][:home_dir] do
  owner "faxocr"
  group "faxocr"
  mode 00775
  action :create
end

template "#{node[:faxocr][:home_dir]}/.profile" do
  source "dot-profile.erb"
  owner "faxocr"
  group "faxocr"
  mode 0644
end

directory "#{node[:faxocr][:home_dir]}/.fonts" do
  owner "faxocr"
  group "faxocr"
  mode 00755
  action :create
end

execute "installing the OCRB fonts" do
  user "faxocr"
  group "faxocr"
  environment("HOME" => node[:faxocr][:home_dir])
  command "install -c -m 444 -o faxocr -g faxocr #{node[:faxocr][:home_dir]}/etc/OCRB.ttf #{node[:faxocr][:home_dir]}/.fonts"
  not_if { ::File.exists?("#{node[:faxocr][:home_dir]}/.fonts/OCRB.ttf") }
end

template "/etc/fonts/conf.avail/09-faxocr-ocrb.conf" do
  source "faxocr-ocrb.conf.erb"
  owner "root"
  group "root"
  mode 0644
end

link "/etc/fonts/conf.d/09-faxocr-ocrb.conf" do
  to "/etc/fonts/conf.avail/09-faxocr-ocrb.conf"
  link_type :symbolic
end

execute "recreating font's cache" do
  user "root"
  group "root"
  command "fc-cache -f"
end

# vim:set expandtab shiftwidth=2 tabstop=2 softtabstop=2:
