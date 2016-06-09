
%w{netpbm autoconf gocr ttf-kochi-gothic xvfb
  libglib2.0-0 libsoup2.4-dev python-webkit python-jswebkit unoconv fetchmail sendemail
  make mpack git subversion zip bzip2 pdftk cron
}.each do |pkg|
  package pkg do
    action :install
  end
end

node[:faxocr][:package][:osdependent].each do |pkg|
  package pkg do
    action :install
  end
end

# vim:set expandtab shiftwidth=2 tabstop=2 softtabstop=2:
