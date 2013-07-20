class ConfigsController < ApplicationController
  before_filter :administrator_only

  def index
    @config_file_path = "#{Rails.root}/../etc/faxocr.conf"
    @raw_config = File.read(@config_file_path)
  end

  def database
    @config_file_path = "#{Rails.root}/config/database.yml"
    @raw_config = File.read(@config_file_path)
  end

  def procfax
    @logdir_path = "#{Rails.root}/../Faxsystem/Log/*"
    @files = Dir.glob(@logdir_path).sort.reverse
  end

  def procfax_exec
    @script_path = "#{Rails.root}/../bin/procfax.sh"
    @log_file_path = "#{Rails.root}/../Faxsystem/Log/rails_procfax.log"

    @result = system("sh " + @script_path + " >> " + @log_file_path)
    @raw_config = File.read(@log_file_path)
  end

private
  def administrator_only
    unless @current_group.is_administrator?
      access_violation
    end
  end
end
