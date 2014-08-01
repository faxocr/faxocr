require 'spec_helper'

%w{netpbm autoconf gocr ttf-vlgothic ttf-kochi-gothic xvfb
	libglib2.0-0 libsoup2.4-dev python-webkit python-jswebkit unoconv fetchmail sendemail
  mpack git subversion zip bzip2 pdftk parallel}.each do |pkg|
  describe package(pkg) do
    it { should be_installed }
  end
end


# vim:set expandtab shiftwidth=2 tabstop=2 softtabstop=2:
