require 'spec_helper'

%w{ruby1.8 rubygems1.8}.each do |pkg|
  describe package(pkg) do
    it { should be_installed }
  end
end

%w{}.each do |pkg|
  describe package(pkg) do
    it { should be_installed.by_gem(pkg) }
  end
end

describe command('gem1.8 -v | grep -w -- 1.3.7') do
  it { should return_exit_status 0 }
end

# vim:set expandtab shiftwidth=2 tabstop=2 softtabstop=2:
