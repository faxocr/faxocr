require 'spec_helper'

%w{fonts-takao}.each do |pkg|
  describe package(pkg) do
    it { should be_installed }
  end
end

%w{65-fonts-takao-gothic.conf 65-fonts-takao-mincho.conf 65-fonts-takao-pgothic.conf 69-msfonts-to-takao.conf}.each do |file|
  describe file("/etc/fonts/conf.avail/#{file}"), :if => os['family'] == 'Debian' do
    it { should be_file }
    it { should be_owned_by 'root' }
    it { should be_grouped_into 'root' }
  end
  describe file("/etc/fonts/conf.d/#{file}"), :if => os['family'] == 'Debian' do
    it { should be_linked_to "/etc/fonts/conf.avail/#{file}"}
  end
end

# vim:set expandtab shiftwidth=2 tabstop=2 softtabstop=2:
