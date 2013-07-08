
require 'chefspec'

describe 'faxocr::default' do
  let (:chef_run) { ChefSpec::ChefRunner.new.converge 'faxocr::default' }
  it 'should install apache' do
    chef_run.should install_package 'apache2'
  end

  it 'should start apache' do
    chef_run.should start_service 'apache2'
  end

end

# vim:set expandtab shiftwidth=2 tabstop=2 softtabstop=2:
