require 'spec_helper'

describe user('www-data') do
  it { should belong_to_group 'faxocr' }
end

%w{rails/files rails/faxocr_config/receive_sheetreader/srml rails/faxocr_config/recognize_sheetreader/srml etc}.each do |d|
  describe file("/home/faxocr/#{d}") do
    it { should be_directory }
    it { should be_readable.by_user('www-data') }
    it { should be_readable.by_user('faxocr') }
    it { should be_writable.by_user('www-data') }
    it { should be_writable.by_user('faxocr') }
    it { should be_executable.by_user('www-data') }
    it { should be_executable.by_user('faxocr') }
  end
end

# vim:set expandtab shiftwidth=2 tabstop=2 softtabstop=2:
