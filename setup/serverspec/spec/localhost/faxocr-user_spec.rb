require 'spec_helper'

describe user('faxocr') do
  it { should exist }
  it { should have_home_directory '/home/faxocr' }
  it { should have_login_shell '/bin/bash' }
end

describe group('faxocr') do
  it { should exist }
end

describe file('/home/faxocr/.profile') do
  it { should be_file }
  it { should be_mode 644 }
  it { should be_owned_by 'faxocr' }
  it { should be_grouped_into 'faxocr' }
end

# vim:set expandtab shiftwidth=2 tabstop=2 softtabstop=2:
