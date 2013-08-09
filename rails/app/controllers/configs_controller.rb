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

    flash[:notice] = '設定を更新しました'

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

  def note
    @config_file_path = "#{Rails.root}/../etc/note.txt"

    if File.exist?(@config_file_path)
      @raw_config = File.read(@config_file_path)
    end
  end

  def note_update
    @body = params[:body].to_s.gsub("\r\n", "\n")

    @config_file_path = "#{Rails.root}/../etc/note.txt"
    File.open(@config_file_path, "w") do |f|
      f.write(@body)
    end

    flash[:notice] = '更新しました'

    redirect_to note_configs_path
  end

  def sendtestfax_exec
    @script_path = "#{Rails.root}/../bin/sendfax"
    @fax_data_pdf = "#{Rails.root}/../etc/test.pdf"
    @log_file_path = "#{Rails.root}/../Faxsystem/Log/rails_sendtestfax.log"
    @dest_fax_num = params[:dest_fax_num].to_s

    @result = system("sh " + @script_path + " #{@dest_fax_num} testOfSedingAfax #{@fax_data_pdf} >> " + @log_file_path)

    if @result == true
      flash[:notice] = '送信しました'
    else
      flash[:notice] = '送信に失敗しました' + "(エラーコード #{$?})"
    end

    redirect_to sendtestfax_configs_path
  end


private
  def administrator_only
    unless @current_user.login_name == 'admin'
      access_violation
    end
  end
end
