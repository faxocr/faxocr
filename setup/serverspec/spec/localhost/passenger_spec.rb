require 'spec_helper'

describe file('/etc/apache2/mods-enabled/passenger.conf') do
  it { should be_file }
end
