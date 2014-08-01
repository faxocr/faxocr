require 'spec_helper'

describe file('/home/faxocr') do
  it { should be_directory }
  it { should be_mode 775 }
  it { should be_owned_by 'faxocr' }
  it { should be_grouped_into 'faxocr' }
end

describe file('/home/faxocr/.fonts') do
  it { should be_directory }
  it { should be_mode 755 }
  it { should be_owned_by 'faxocr' }
  it { should be_grouped_into 'faxocr' }
end

describe file('/home/faxocr/.fonts/OCRB.ttf') do
  it { should be_file }
  it { should be_mode 444 }
  it { should be_owned_by 'faxocr' }
  it { should be_grouped_into 'faxocr' }
end

# vim:set expandtab shiftwidth=2 tabstop=2 softtabstop=2:
