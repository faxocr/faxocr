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

  def update_system_config
    @body = params[:body].join.to_s.gsub("\r\n", "\n")

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

  def view_procfax_log
    @page_size = page_size
    @logdir_path = "#{Rails.root}/../Faxsystem/Log/*"
    files = Dir.glob(@logdir_path).sort.reverse
    @str = get_summary_of_procfax_log(files, @page_size)
  end

  def download_all_procfax_log
    @logdir_path = "#{Rails.root}/../Faxsystem/Log/*"
    files = Dir.glob(@logdir_path).sort.reverse
    str = get_summary_of_procfax_log(files, -1)
    respond_to do |format|
      format.text { send_data str, :filename => "procfax_summary_log.txt"}
    end
  end

  def procfax_exec
    @script_path = "#{Rails.root}/../bin/procfax.sh"
    log_file_path = "#{Rails.root}/../Faxsystem/Log/rails_procfax.log"
    @raw_config = `sh #{@script_path}`
    @result = $?.exitstatus == 0 ? true : false
    open(log_file_path, "a") do |f|
      f.flock(File::LOCK_EX)
      f.puts(@raw_config)
    end
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
    redirect_to view_faxmail_queue_configs_path
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
    @body = params[:body].join.to_s.gsub("\r\n", "\n").to_s

    @config_file_path = "#{Rails.root}/../etc/note.txt"
    File.open(@config_file_path, "w") do |f|
      f.write(@body)
    end

    flash[:notice] = '更新しました'

    redirect_to note_configs_path
  end

  def view_sendfax_log
    @page_size = page_size
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
      redirect_to view_sendfax_log_configs_path
      return
    end

    @dest_fax_num = @dest_fax_num.gsub("-", "")
    @result = system("sh " + @script_path + " #{@dest_fax_num} testOfSedingAfax #{@fax_data_pdf} >> " + @log_file_path)

    if @result == true
      flash[:notice] = '送信しました'
    else
      flash[:notice] = '送信に失敗しました' + "(エラーコード #{$?})"
    end

    redirect_to view_sendfax_log_configs_path
  end

  def view_faxmail_queue
    @raw_config = `ls -lt #{Rails.root}/../Maildir/new | tail -n +2 | cat -n`

    if @raw_config.length == 0
      @raw_config = "[No new Fax]"
    end

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

  def view_answer_sheet
    # index for all user
    group_id = 1
    survey_id = 1
    @group = Group.find(group_id)
    @survey = @group.surveys.find(survey_id)
    sheet_ids = @survey.sheet_ids
    @answer_sheets = AnswerSheet.where(:sheet_id => sheet_ids).order(date: :desc)
  end

private
  def administrator_only
    unless @current_user.login_name == 'admin'
      access_violation
    end
  end

  def get_summary_of_procfax_log(files, show_lines)
    str = ""
    cnt = 0
    show_all = show_lines == -1 ? true : false
    files.each do |file|
      if File.ftype(file) == "directory" && (show_all or cnt < show_lines.to_i)
        next unless File.exist?("#{file}/procfax.log")
        contents = IO.readlines("#{file}/procfax.log")
        count_f = contents.map{|l| l if l.include?("INFO: Number of fax processed:")}.join.chomp.split(':')[-1]
        count_fe = contents.map{|l| l if l.include?("INFO: Number of error occurred processing fax:")}.join.chomp.split(':')[-1]
        count_se = contents.map{|l| l if l.include?("INFO: Number of error occurred processing sheet:")}.join.chomp.split(':')[-1]
        count_s = contents.map{|l| l if l.include?("INFO: Number of sheet processed:")}.join.split(':')[-1]
        count_s = count_s.to_i - count_se.to_i
        str += File.basename(file).gsub(/(\d{4})(\d{2})(\d{2})(\d{2})(\d{2})(\d{0,2})/, '\1/\2/\3 \4:\5　')
        str += "到着Fax " + count_f.to_s + "件 (Fax処理失敗 " + count_fe.to_s +
           " / シート処理失敗 " + count_se.to_s + ") →  " + count_s.to_s +
           " シート受理\r\n"
        cnt += 1
      end
    end
    return str
  end
end
