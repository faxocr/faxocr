
package "fetchmail" do
  action :install
end

execute "create a empty file of Maildir/fetchmail.log" do
  user "faxocr"
  group "faxocr"
  command "touch #{node[:faxocr][:home_dir]}/Maildir/fetchmail.log"
  not_if { ::File.exists?("touch #{node[:faxocr][:home_dir]}/Maildir/fetchmail.log") }
end

# vim:set expandtab shiftwidth=2 tabstop=2 softtabstop=2:
