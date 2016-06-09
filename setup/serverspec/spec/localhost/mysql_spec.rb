require 'spec_helper'

describe package('mysql-server') do
  it { should be_installed }
end

describe file('/etc/mysql/conf.d/faxocr.cnf') do
  it { should be_file }
  it { should be_mode 644 }
  it { should be_owned_by 'root' }
  it { should be_grouped_into 'root' }
  it { should be_readable.by_user('root') }
  it { should be_readable.by_user('mysql') }
end

# need to check more whether db and tables exist

describe service('mysql') do
  it { should be_enabled }
  #it { should be_running }
end

describe command('echo quit |  mysql -u faxocr --password=faxocr faxocr_development') do
  its(:exit_status) { should eq 0 }
end

# vim:set expandtab shiftwidth=2 tabstop=2 softtabstop=2:
