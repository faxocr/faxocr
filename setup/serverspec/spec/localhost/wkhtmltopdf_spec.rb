require 'spec_helper'

describe file('/usr/local/bin/wkhtmltopdf') do
  it { should be_file }
  it { should be_owned_by 'root' }
  it { should be_executable.by_user('faxocr') }
  it { should be_executable.by_user('www-data') }
end

# vim:set expandtab shiftwidth=2 tabstop=2 softtabstop=2:
