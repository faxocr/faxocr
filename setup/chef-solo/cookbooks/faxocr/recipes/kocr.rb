include_recipe 'faxocr::libtool'

git "#{node[:faxocr][:home_dir]}/src/kocr" do
  repository "https://github.com/faxocr/kocr"
  reference "master"
  action :sync
  user "faxocr"
  group "faxocr"
end

execute "compiling kocr" do
  cwd "#{node[:faxocr][:home_dir]}/src/kocr/src"
  user "faxocr"
  group "faxocr"
  command "make"
end

bash "installing kocr binary and libraries to the system" do
  cwd "#{node[:faxocr][:home_dir]}/src/kocr/src"
  user "root"
  group "root"
  code <<-EOH
    make install
    ldconfig
    EOH
  not_if { ::File.exists?("/usr/local/lib/libkocr.a") and ::File.mtime("/usr/local/lib/libkocr.a") >= ::File.mtime("#{node[:faxocr][:home_dir]}/src/kocr/src/.libs/libkocr.a") }
end

execute "installing kocr's all DBs" do
  cwd "#{node[:faxocr][:home_dir]}/src/kocr/databases"
  user "faxocr"
  group "faxocr"
  command "install -c -m 444 -o faxocr -g faxocr *.db *.xml #{node[:faxocr][:home_dir]}/etc"
  not_if { ::File.exists?("#{node[:faxocr][:home_dir]}/etc/list-num.xml") and ::File.mtime("#{node[:faxocr][:home_dir]}/etc/list-num.xml") >= ::File.mtime("#{node[:faxocr][:home_dir]}/src/kocr/databases/list-num.xml") }
end

bash "replacing kocr's num DB with numocrb DB" do
  cwd "#{node[:faxocr][:home_dir]}/src/kocr/databases"
  user "faxocr"
  group "faxocr"
  code <<-EOH
    install -c -m 444 -o faxocr -g faxocr list-numocrb.db  #{node[:faxocr][:home_dir]}/etc/list-num.db
    install -c -m 444 -o faxocr -g faxocr list-numocrb.xml #{node[:faxocr][:home_dir]}/etc/list-num.xml
    EOH
end

# vim:set expandtab shiftwidth=2 tabstop=2 softtabstop=2:
