require 'spec_helper'

describe command('/home/faxocr/bin/fonttest.sh') do
  it { should return_stdout /^found OCRB font/ }
end

describe command('su -s /bin/sh -c /home/faxocr/bin/fonttest.sh -l faxocr') do
  it { should return_stdout /^found OCRB font/ }
end

# vim:set expandtab shiftwidth=2 tabstop=2 softtabstop=2:
