require 'spec_helper'

describe host('8.8.8.8') do
  it { should be_reachable }
end

# vim:set expandtab shiftwidth=2 tabstop=2 softtabstop=2:
