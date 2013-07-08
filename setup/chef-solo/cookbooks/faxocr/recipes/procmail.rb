
package "procmail" do
  action :install
end

template "#{node[:faxocr][:home_dir]}/.forward" do
  source "dot-forward.erb"
  owner "faxocr"
  group "faxocr"
  mode 0644
end

template "#{node[:faxocr][:home_dir]}/.procmailrc" do
  source "procmailrc.erb"
  owner "faxocr"
  group "faxocr"
  mode 0644
end


# vim:set expandtab shiftwidth=2 tabstop=2 softtabstop=2:
