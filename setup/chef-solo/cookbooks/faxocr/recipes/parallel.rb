
if node[:parallel][:usepackage] == true
  package "parallel" do
    action :install
  end
else
  remote_file "/tmp/parallel-#{node[:parallel][:version]}.tar.bz2" do
    source "http://ftpmirror.gnu.org/parallel/parallel-#{node[:parallel][:version]}.tar.bz2"
  end

  execute "extracting GNU parallel" do
    cwd "/tmp"
    command "tar jxf parallel-#{node[:parallel][:version]}.tar.bz2"
    not_if { ::File.exists?("/tmp/parallel-#{node[:parallel][:version]}") }
  end

  bash "making and installing GNU parallel" do
    cwd "/tmp/parallel-#{node[:parallel][:version]}"
    code <<-EOH
      ./configure
      make install
    EOH
    not_if { ::File.exists?("/usr/local/bin/parallel") }
  end
end

# vim:set expandtab shiftwidth=2 tabstop=2 softtabstop=2:
