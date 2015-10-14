
default[:parallel][:version] = "20150922"

`apt-cache search '^parallel$' | grep '^parallel - '`
if $? == 0
  default[:parallel][:usepackage] = true
else
  default[:parallel][:usepackage] = false
end

# vim:set expandtab shiftwidth=2 tabstop=2 softtabstop=2:
