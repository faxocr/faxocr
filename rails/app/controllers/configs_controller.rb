# -*- coding: utf-8 -*-
class ConfigsController < ApplicationController
  before_filter :administrator_only

  def index
    @config_file_path = "#{Rails.root}/../etc/faxocr.conf"
    @raw_config = File.read(@config_file_path)
  end

  def update
    @body = params[:body].to_s.gsub("\r\n", "\n")

    @config_file_path = "#{Rails.root}/../etc/faxocr.conf"
    File.open(@config_file_path, "w") do |f|
      f.write(@body)
    end

    redirect_to configs_path
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

  def cron
    @raw_config = `crontab -u faxocr -l`
  end

private
  def administrator_only
    unless @current_user.login_name == 'admin'
      access_violation
    end
  end
end
