require 'spec_helper'

describe package('procmail') do
  it { should be_installed }
end

describe file('/usr/bin/procmail') do
  it { should be_file }
  it { should be_executable }
end

describe file('/home/faxocr/.forward') do
  it { should be_file }
  it { should be_owned_by 'faxocr' }
  it { should be_grouped_into 'faxocr' }
  it { should be_readable.by_user('faxocr') }
  it { should be_readable.by_user('postfix') }
end

describe file('/home/faxocr/.procmailrc') do
  it { should be_file }
  it { should be_owned_by 'faxocr' }
  it { should be_grouped_into 'faxocr' }
  it { should be_readable.by_user('faxocr') }
  it { should be_readable.by_user('postfix') }
  it { should contain "everynet.jp" }
end


#
# vim:set expandtab shiftwidth=2 tabstop=2 softtabstop=2:
