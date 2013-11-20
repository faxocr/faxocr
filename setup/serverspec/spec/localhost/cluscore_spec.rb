require 'spec_helper'

describe file('/home/faxocr/src/cluscore') do
  it { should be_directory }
  it { should be_mode 755 }
  it { should be_owned_by 'faxocr' }
  it { should be_grouped_into 'faxocr' }
end

describe file('/usr/local/lib/libcluscore.a') do
  it { should be_file }
  it { should be_owned_by 'root' }
end

# vim:set expandtab shiftwidth=2 tabstop=2 softtabstop=2:
