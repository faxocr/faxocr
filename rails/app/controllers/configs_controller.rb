# -*- coding: utf-8 -*-
class ConfigsController < ApplicationController
  before_filter :administrator_only

  def index
    redirect_to note_configs_path
  end

  def view_system_config
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

    redirect_to view_system_config_configs_path
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

  def getfax_exec
    @script_path = "#{Rails.root}/../bin/getfax"
    @log_file_path = "#{Rails.root}/../Faxsystem/Log/rails_procfax.log"

    @result = system("sh " + @script_path + " >> " + @log_file_path)
    case @result
    when true
      flash[:notice] = 'Faxを取得(fetchmail)しました'
    when false
      exit_status = $?.to_i / 256
      case exit_status
      when 1
        flash[:notice] = '新着Faxはありませんでした'
      when 3
        flash[:notice] = 'fetchmailでのユーザ認証に失敗しました。faxocr.confの設定を見直してください。'
      else
        flash[:notice] = 'fetchmailがエラーを返しました' + "(エラーコード #{exit_status})"
      end
    when nil
      flash[:notice] = 'getfaxコマンドの実行に失敗しました' + "(エラーコード #{$?})"
    end
    redirect_to viewmaildir_configs_path
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

  def sendtestfax
    @log_file_path = "#{Rails.root}/../Faxsystem/Log/sendfax.log"
    @raw_config = `tail -10 #{@log_file_path}`
  end

  def sendtestfax_exec
    @script_path = "#{Rails.root}/../bin/sendfax"
    @fax_data_pdf = "#{Rails.root}/../etc/test.pdf"
    @log_file_path = "#{Rails.root}/../Faxsystem/Log/rails_sendtestfax.log"
    @dest_fax_num = params[:dest_fax_num].to_s

    if /[^0-9-]/ =~ @dest_fax_num
      flash[:notice] = '電話番号には、半角数値と「-」のみをお使いください'
      redirect_to sendtestfax_configs_path
      return
    end

    @dest_fax_num = @dest_fax_num.gsub("-", "")
    @result = system("sh " + @script_path + " #{@dest_fax_num} testOfSedingAfax #{@fax_data_pdf} >> " + @log_file_path)

    if @result == true
      flash[:notice] = '送信しました'
    else
      flash[:notice] = '送信に失敗しました' + "(エラーコード #{$?})"
    end

    redirect_to sendtestfax_configs_path
  end

  def viewmaildir
    @raw_config = `ls -lt #{Rails.root}/../Maildir/new`

    @config_file_path = "#{Rails.root}/../etc/faxocr.conf"
    @result = system("sh -c '. " + @config_file_path + '; test "$FAX_RECV_SETTING" = "pop3" && exit 0; exit 1 \'')
    case @result
    when true
      @fetchmail_enabled = 1
    when false
      @fetchmail_enabled = 0
    when nil
      @fetchmail_enabled = 0
      flash[:notice] = "設定ファイルからFax受信方法の取得に失敗しました(エラーコード #{$?})"
    end
  end


private
  def administrator_only
    unless @current_user.login_name == 'admin'
      access_violation
    end
  end
end
