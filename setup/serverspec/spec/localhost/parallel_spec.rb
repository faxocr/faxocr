require 'spec_helper'

`apt-cache search '^parallel$' | grep '^parallel - '`
if $? == 0
  describe package('parallel') do
    it { should be_installed }
  end
else
  describe file('/usr/local/bin/parallel') do
    it { should be_file }
    it { should be_owned_by 'root' }
    it { should be_grouped_into 'root' }
    it { should be_executable.by_user('faxocr') }
    it { should be_executable.by_user('www-data') }
  end
end

# vim:set expandtab shiftwidth=2 tabstop=2 softtabstop=2:
