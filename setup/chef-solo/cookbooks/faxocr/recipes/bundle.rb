
git "cluscore" do
  repository "https://github.com/cluscore/cluscore"
  destination "#{node[:faxocr][:home_dir]}/src/cluscore"
  action :sync
  user "faxocr"
  group "faxocr"
end

bash "install_cluscore" do
  cwd "#{node[:faxocr][:home_dir]}/src/cluscore"
  code <<-EOH
    ./configure --prefix=/usr/local
    make && make install
    EOH
end

# vim:set expandtab shiftwidth=2 tabstop=2 softtabstop=2:
