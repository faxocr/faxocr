
cron "processing fax" do
  minute "0-59/10"
  hour "*"
  day "*"
  month "*"
  weekday "*"
  command "#{node[:faxocr][:home_dir]}/bin/procfax.sh >> Faxsystem/Log/cron_procfax.log"
  user "faxocr"
  mailto "root"
  path "/bin:/sbin:/usr/bin:/usr/sbin:/usr/local/bin:/usr/local/sbin:#{node[:faxocr][:home_dir]}/bin"
  home node[:faxocr][:home_dir]
  shell "/bin/sh"
end

cron "processing reports" do
  minute "0-59/15"
  hour "*"
  day "*"
  month "*"
  weekday "*"
  command "#{node[:faxocr][:home_dir]}/bin/procreport.sh >> Faxsystem/Log/cron_procreport.log"
  user "faxocr"
  #
  # following parameters depend on the above entry
  #
  #mailto "root"
  #path "/bin:/sbin:/usr/bin:/usr/sbin:/usr/local/bin:/usr/local/sbin:#{node[:faxocr][:home_dir]}/bin"
  #home node[:faxocr][:home_dir]
  #shell "/bin/sh"
end

# vim:set expandtab shiftwidth=2 tabstop=2 softtabstop=2:
