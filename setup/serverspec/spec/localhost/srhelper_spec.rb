require 'spec_helper'

describe file('/home/faxocr/bin/srhelper') do
  it { should be_file }
  it { should be_mode 755 }
  it { should be_owned_by 'faxocr' }
  it { should be_grouped_into 'faxocr' }
  it { should be_executable.by_user('faxocr') }
  it { should be_executable.by_user('www-data') }
end

# vim:set expandtab shiftwidth=2 tabstop=2 softtabstop=2:
