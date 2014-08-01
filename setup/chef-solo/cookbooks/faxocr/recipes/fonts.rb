package "fonts-takao" do
  action :install
end

%w{65-fonts-takao-gothic.conf 65-fonts-takao-mincho.conf 65-fonts-takao-pgothic.conf}.each do |file|
  template "/etc/fonts/conf.avail/#{file}" do
    source file
    owner "root"
    group "root"
    mode 0644
    only_if { platform?('debian') }
  end
  link "/etc/fonts/conf.d/#{file}" do
    to "/etc/fonts/conf.avail/#{file}"
    link_type :symbolic
    only_if { platform?('debian') }
  end
end

template "/etc/fonts/conf.avail/69-msfonts-to-takao.conf" do
  source "msfonts-to-takao.conf.erb"
  owner "root"
  group "root"
  mode 0644
end

link "/etc/fonts/conf.d/69-msfonts-to-takao.conf" do
  to "/etc/fonts/conf.avail/69-msfonts-to-takao.conf"
  link_type :symbolic
end

execute "recreating font's cache" do
  user "root"
  group "root"
  command "fc-cache -f"
end

# vim:set expandtab shiftwidth=2 tabstop=2 softtabstop=2:
