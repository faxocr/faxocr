
git "#{node[:faxocr][:home_dir]}/src" do
  repository "https://code.google.com/p/sheet-reader"
  reference "master"
  destination "#{node[:faxocr][:home_dir]}/src/sheet-reader"
  action :sync
  user "faxocr"
  group "faxocr"
end

bash "compiling sheetreader" do
  cwd "#{node[:faxocr][:home_dir]}/src/sheet-reader"
  code <<-EOH
    ./configure CFLAGS="-O3" --prefix=/usr/local
    make
    EOH
end

execute "installing sheetreader" do
  cwd "#{node[:faxocr][:home_dir]}/src/sheet-reader"
  command "make install"
  not_if { ::File.exists?("/usr/local/bin/sheetreader") and ::File.mtime("/usr/local/bin/sheetreader") >= ::File.mtime("#{node[:faxocr][:home_dir]}/src/sheet-reader/src/sheetreader") }
end

# vim:set expandtab shiftwidth=2 tabstop=2 softtabstop=2:
