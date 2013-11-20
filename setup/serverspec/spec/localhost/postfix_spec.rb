require 'spec_helper'

describe package('postfix') do
  it { should be_installed }
end

describe service('postfix') do
  it { should be_enabled  }
  it { should be_running  }
end

describe port(25) do
  it { should be_listening }
end

