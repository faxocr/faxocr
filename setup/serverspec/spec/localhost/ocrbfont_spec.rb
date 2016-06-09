require 'spec_helper'

describe command('/home/faxocr/bin/fonttest.sh') do
  its(:stdout) { should match /^found OCRB font/ }
end

describe command('su -s /bin/sh -c /home/faxocr/bin/fonttest.sh -l faxocr') do
  its(:stdout) { should match /^found OCRB font/ }
end

# vim:set expandtab shiftwidth=2 tabstop=2 softtabstop=2:
