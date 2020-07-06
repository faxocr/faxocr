require 'spec_helper'


os_dependent_packages =
  case os[:family]
  when "debian"
    if os[:release].to_f >= 8.0
      %w{ fonts-vlgothic }
    else
      %w{ ttf-vlgothic }
    end
  when "ubuntu"
    if os[:release].to_f >= 14.04
      %w{ fonts-vlgothic }
    else
      %w{ ttf-vlgothic }
    end
  else
    %w{ }
  end

os_dependent_packages.each do |pkg|
  describe package(pkg) do
    it { should be_installed }
  end
end


%w{netpbm autoconf gocr ttf-kochi-gothic xvfb
	libglib2.0-0 libsoup2.4-dev python-webkit python-jswebkit unoconv fetchmail sendemail
  mpack git subversion zip bzip2 poppler-utils cron}.each do |pkg|
  describe package(pkg) do
    it { should be_installed }
  end
end


# vim:set expandtab shiftwidth=2 tabstop=2 softtabstop=2:
