require 'spec_helper'

%w{ruby1.9.1}.each do |pkg|
  describe package(pkg) do
    it { should be_installed }
  end
end

%w{}.each do |pkg|
  describe package(pkg) do
    it { should be_installed.by_gem(pkg) }
  end
end

# vim:set expandtab shiftwidth=2 tabstop=2 softtabstop=2:
