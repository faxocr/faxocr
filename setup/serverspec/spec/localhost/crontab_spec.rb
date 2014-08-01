require 'spec_helper'

describe cron do
  it { should have_entry('0-59/10 * * * * /home/faxocr/bin/procfax.sh >> Faxsystem/Log/cron_procfax.log').with_user('faxocr') }
  it { should have_entry('0-59/15 * * * * /home/faxocr/bin/procreport.sh >> Faxsystem/Log/cron_procreport.log').with_user('faxocr') }
end

# vim:set expandtab shiftwidth=2 tabstop=2 softtabstop=2:
