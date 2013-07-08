
subversion "cluscore" do
  repository "http://cluscore.googlecode.com/svn/trunk/"
  revision "HEAD"
  action :sync
  destination "#{node[:faxocr][:home_dir]}/src/cluscore"
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
