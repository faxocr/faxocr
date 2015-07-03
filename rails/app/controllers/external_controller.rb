# -*- coding: utf-8 -*-
require "net/smtp"

class ExternalController < ApplicationController

  skip_before_filter :verify_authenticity_token ,:only => [:reg_upload, :reg_exec, :sht_field, :sht_script, :sht_marker, :sht_config, :sht_verify, :sht_commit, :sht_field_checker]

  # file size limit
  # @@size_limit = 60000
  @@size_limit = 128 * 1024

  # コメント：
  #
  # - もっとよいpath指定の方法がありそうだが...
  # - renderは、app/views/external/dummy.erbというテンプレファイルの
  #   呼び出しを意味している
  # - methodを足す場合には、conrig/route.rbにroute設定を行う

  # http://server_addr:3000/external/phpinfo
  def php_info

    # PHP CLI SAPIの呼び出しとなるため、HTML出力が無い
    @html = "<PRE>\n"
    @html += `cd ./app/external; php contrib/test.php debug_mode=\"#{debug_mode}\"`
    @html += "<PRE>\n"

    render :dummy
  end

  # http://server_addr:3000/external/test
  def test

    # PHP CLI SAPIだが、スクリプトがHTMLタグを生成
    # ただし、各種imgやcssはpublic/external側にも用意

    @html = "<PRE>\n"
    # @html += "RAILS_ENV=#{fetch(:rails_env)}"
    # @html = `cd ./app/external; php form-marker.php`
    @html += "RAILS_ENV=#{Rails.env}"
    @html += "<PRE>\n"

    render :dummy
  end

  #
  # http://server_addr:3000/external/register/1234
  #
  def register
    # XXX
    # @html = "GID: " + @gid + "\n<BR>"
    # @action = "/external/register/" + @gid + "?status=list"
    # @action = "/external/test/" + @gid

    @limit = (@@size_limit / 1024).to_s + "K"
    # @limit = "128K"

    @gid = params[:group_id].nil? ? params[:id] : params[:group_id]
    if @gid.nil? then
      redirect_to :controller => 'faxocr'
      return
    end
    @action = "/external/reg_upload/"
  end

  def reg_upload

    @gid = params[:gid]

    if @gid.nil? then
      redirect_to :controller => 'faxocr'
      return
    end

    if params[:file].nil? then
      redirect_to :controller => 'external', :action => 'register', :id => @gid
      return
    else
      @filename = params[:file]['upfile'].original_filename
      @size = params[:file]['upfile'].size
      @tname = @gid + "-" + Time.now().strftime("%Y%m%d%H%M")
    end

    if 0 < @size && @size <= @@size_limit && /\.xls$/ =~ @filename then
      File.open("#{Rails.root}/files/#{@tname}" + ".lst", "wb") {
        |f| f.write(params[:file]['upfile'].read)
      }
      # @html = "GID: " + @gid + "\n<BR>" +
      #  "Filename: " + @filename + " (" + @size.to_s + ")\n<BR>" +
      #  "Temp file: " + @tname
      # @html = `cd ./app/external; php reg_upload.php`
      # @html = `echo php reg_upload.php file=\"#{@tname}\"`
      @html = `cd ./app/external; php reg_upload.php gid=\"#{@gid}\" file=\"#{@tname}\" rails_env=\"#{Rails.env}\" debug_mode=\"#{debug_mode}\"`
      render :dummy
    else
      # @html = "GID: " + @gid + "\n<BR>"
      flash[:notice] = "ファイルが不正です・サイズや拡張子を確認して下さい"
      redirect_to :controller => 'faxocr'
    end
  end

  def reg_exec

    @file = params[:file]
    @gid = params[:gid]
    @group = Group.find(params[:gid])
    @result = `ruby #{Rails.root}/files/#{@file}.rb #{Rails.root} #{@gid}`

    flash[:notice] = "調査対象を一括登録しました"
    redirect_to group_candidates_url(@group)
  end

  # Debug
  # http://server_addr:3000/external/sheet_checker
  def sheet_checker
    # 生成したpdfは、public/external/pdfに入れる方針で
    @limit = (@@size_limit / 1024).to_s + "K"

    @debug_mode = debug_mode;

    @action = "/external/sht_field_checker/"
  end

  # Debug
  def sht_field_checker
    @tname = params[:file_id]

    @msg = flash[:notice]

    if not @tname.nil? then
      @html = `cd ./app/external; php sht_field_checker.php file=\"#{@tname}\" msg=\"#{@msg}\" debug_mode=\"#{debug_mode}\"`
      render :dummy
      return
    else
      if params[:file].nil? then
        redirect_to :controller => 'external', :action => 'sheet_checker'
        return
      else
        @filename = params[:file]['upfile'].original_filename
        @size = params[:file]['upfile'].size
        @tname = "Debug-" + Time.now().strftime("%Y%m%d%H%M")
      end
    end

    if 0 < @size && @size <= @@size_limit && /(\.xlsx?)$/ =~ @filename then
      ext = $1
      outputFileName = "#{Rails.root}/files/#{@tname}"
      outputFileNameWithExt = outputFileName + ext
      File.open(outputFileNameWithExt, "wb") {
        |f| f.write(params[:file]['upfile'].read)
      }
      if ext == ".xlsx" then
        `unoconv -f xls #{outputFileNameWithExt}`
      end
      @filename.slice!(/\.\w+$/)
      @html = `cd ./app/external; php sht_field_checker.php file=\"#{@tname}\" sname=\"#{@filename}\" debug_mode=\"#{debug_mode}\"`
      render :dummy
    else
      flash[:notice] = "ファイルが不正です・サイズや拡張子を確認して下さい"
      redirect_to :controller => 'faxocr'
    end
  end

  # http://server_addr:3000/external/sheet/1234/5678
  def sheet

    # 生成したpdfは、public/external/pdfに入れる方針で
    # @gid = params[:group_id]
    @gid = params[:group_id].nil? ? params[:id] : params[:group_id]
    @sid = params[:survey_id]

    @limit = (@@size_limit / 1024).to_s + "K"

    @debug_mode = debug_mode;

    @action = "/external/sht_field/"

    # @html = "GID: " + @gid + "\n<BR>"
    # @html += "SID: " + @sid + "\n<BR>"
    # render :dummy
  end

  def sht_field

    @gid = params[:gid]
    @sid = params[:sid]
    @target = params[:target]
    @tname = params[:file_id]
    @group = Group.find(params[:gid])
    @candidates = @group.candidates

    @msg = flash[:notice]

    if not @tname.nil? then
      @html = `cd ./app/external; php sht_field.php gid=\"#{@gid}\" sid=\"#{@sid}\" file=\"#{@tname}\" msg=\"#{@msg}\" target=\"#{@target}\" debug_mode=\"#{debug_mode}\"`
      render :dummy
      return
    else
      if params[:file].nil? then
        redirect_to :controller => 'external', :action => 'sheet', :group_id => @gid, :survey_id => @sid
        return
      else
        @filename = params[:file]['upfile'].original_filename
        @size = params[:file]['upfile'].size
        @tname = @gid + "-" + Time.now().strftime("%Y%m%d%H%M")
      end
    end

    if @target == "registered" && @candidates.size == 0 then
        flash[:notice] = "設定済み調査対象がありません"
        redirect_to :controller => 'faxocr'
    elsif 0 < @size && @size <= @@size_limit && /(\.xlsx?)$/ =~ @filename then
      ext = $1
      outputFileName = "#{Rails.root}/files/#{@tname}"
      outputFileNameWithExt = outputFileName + ext
      File.open(outputFileNameWithExt, "wb") {
        |f| f.write(params[:file]['upfile'].read)
      }
      if ext == ".xlsx" then
        `unoconv -f xls #{outputFileNameWithExt}`
      end
      # @html = "GID: " + @gid + "\n<BR>" +
      #  "Filename: " + @filename + " (" + @size.to_s + ")\n<BR>" +
      #  "Temp file: " + @tname
      # @html = `cd ./app/external; php reg_upload.php`
      # @html = `echo php reg_upload.php file=\"#{@tname}\"`
      @filename.slice!(/\.\w+$/)
      @html = `cd ./app/external; php sht_field.php gid=\"#{@gid}\" sid=\"#{@sid}\" file=\"#{@tname}\" target=\"#{@target}\" sname=\"#{@filename}\" debug_mode=\"#{debug_mode}\"`
      render :dummy
    else
      flash[:notice] = "ファイルが不正です・サイズや拡張子を確認して下さい"
      redirect_to :controller => 'faxocr'
    end
  end

  def sht_script

    @gid = params[:gid]
    @sid = params[:sid]
    @fileid = params[:fileid]
    @target = params[:target]

    @param_str = ""
    params.each {|key, value|
      quoted_value = value.gsub('"', '\"')
      @param_str += key + "=\"" + quoted_value + "\" "
    }
    @errmsg = `cd ./app/external; php sht_script.php #{@param_str} file_id=#{@fileid} debug_mode=\"#{debug_mode}\"`
    # flash[:notice] = @errmsg
    # flash[:notice] = "セーブしました"

    # @html = "<PRE>\n"
    # @html += @param_str + "\n"
    # @html += "<PRE>\n"
    # render :dummy

    redirect_to :controller => 'external', :action => 'sht_marker', :params => {:gid => @gid, :sid => @sid, :file_id => @fileid}
  end

  def sht_marker

    @gid = params[:gid]
    @sid = params[:sid]
    @file = params[:fileid]
    @msg = flash[:notice]

    @file = @file.nil? ? params[:file_id] : @file
    # @group = Group.find(params[:gid])

    @html = `cd ./app/external; php sht_marker.php gid=\"#{@gid}\" sid=\"#{@sid}\" file=\"#{@file}\" msg=\"#{@msg}\" debug_mode=\"#{debug_mode}\"`
    render :dummy
  end

  def sht_config

    @gid = params[:gid]
    @sid = params[:sid]
    @file = params[:fileid]
    @target = params[:target]
    @group = Group.find(params[:gid])
    @candidates = @group.candidates

    @param_str = ""
    params.each {|key, value|
      @param_str += key + "=\"" + value + "\" "
    }

    # @html = "<PRE>\n"
    # @html += @param_str + "\n"
    # @html += "<PRE>\n"
    # @html += @ret
    # render :dummy

    @param_str += "candidate_code=\""
    @param_str += "00000-"
    if @target == "1"
      @candidates.each do |candidate|
        if candidate.candidate_code =~ /\d{5}/ && candidate.candidate_code != "00000"
          @param_str += candidate.candidate_code + "-"
        end
      end
    end
    @param_str += "\""

    if (params[:func]) then
      @file_html = "#{Rails.root}/files/#{@file}.html"
      @file_prefix = "#{Rails.root}/files/#{@gid}-#{@sid}"
      @orient = params[:orient]

      @ret = `cd #{Rails.root}; xvfb-run -a wkhtmltopdf --page-size A4 -O #{@orient} #{@file_html} #{@file_prefix}.pdf`
      @ret = `pdftk #{@file_prefix}.pdf cat 2-end output #{@file_prefix}-00000.pdf`
      @ret = `pdftk #{@file_prefix}-00000.pdf burst output #{@file_prefix}-%05d.pdf`
      @ret = `rm -f #{@file_prefix}-00000.pdf`
      @ret = `convert #{@file_prefix}-00001.pdf #{@file_prefix}.png`
      @ret = `rm -f #{@file_prefix}.zip; zip -j #{@file_prefix}.zip #{@file_prefix}-*.pdf`
      @ret = `cp #{@file_prefix}-00001.pdf #{@file_prefix}.pdf`
      @ret = `rm -f #{@file_prefix}-*.pdf`
      @ret = `rm -f doc_data.txt`
      @ret = `cp -p #{@file_html} #{@file_prefix}.html`
    else
      @ret = `cd ./app/external; php sht_config.php file=\"#{@file}\" #{@param_str} rails_env=\"#{Rails.env}\" debug_mode=\"#{debug_mode}\"`
      # flash[:notice] = @errmsg
      # flash[:notice] = "セーブしました"
    end

    redirect_to :controller => 'external', :action => 'sht_marker', :params => {:gid => @gid, :sid => @sid, :file_id => @file}
  end

  def sht_verify

    @gid = params[:gid]
    @sid = params[:sid]
    @file = params[:fileid]
    @msg = flash[:notice]

    @html = `cd ./app/external; php sht_verify.php gid=\"#{@gid}\" sid=\"#{@sid}\" file=\"#{@file}\" msg=\"#{@msg}\" debug_mode=\"#{debug_mode}\"`
    render :dummy
  end

  def sht_commit

    @gid = params[:gid]
    @sid = params[:sid]
    @file = params[:fileid]
    @group = Group.find(params[:gid])

    @result = `ruby #{Rails.root}/files/#{@file}.rb #{Rails.root} #{@gid}`

    if debug_mode == 'true'
      @file_prefix = "#{Rails.root}/files/#{@gid}-#{@sid}"
      @debug = `cd #{Rails.root}/files/`
      @debug = `convert #{@file_prefix}.pdf #{@file_prefix}.tif`
      sendmail "#{@file_prefix}.tif"
    end

    flash[:notice] = "シートを登録しました"
    redirect_to group_url(@group)
  end

  def download

    @gid = params[:group_id].nil? ? params[:id] : params[:group_id]
    @sid = params[:survey_id]
    @file_pdf = "#{Rails.root}/files/#{@gid}-#{@sid}.pdf"

    # send_file("#{Rails.root}/files/simple.zip",
    #          {:filename => "simple.zip",
    #           :type => "application/zip"})

    # send_file("#{Rails.root}/files/simple.pdf",
    #           {:filename => "simple.pdf",
    #             :type => "application/pdf"})

    send_file(@file_pdf,
              {:filename => "#{@gid}-#{@sid}.pdf",
                :type => "application/pdf"})
  end

  def download_zip

    @gid = params[:group_id].nil? ? params[:id] : params[:group_id]
    @sid = params[:survey_id]
    @file_zip = "#{Rails.root}/files/#{@gid}-#{@sid}.zip"

    send_file(@file_zip,
              {:filename => "#{@gid}-#{@sid}.zip",
                :type => "application/zip"})
  end

  def download_html

    @gid = params[:group_id].nil? ? params[:id] : params[:group_id]
    @sid = params[:survey_id]
    @file_html = "#{Rails.root}/files/#{@gid}-#{@sid}.html"

    send_file(@file_html,
              {:filename => "#{@gid}-#{@sid}.html",
                :type => "text/html"})
  end

  def getimg

    @gid = params[:group_id]
    @sid = params[:survey_id]
    @file_png = "#{Rails.root}/files/#{@gid}-#{@sid}.png"

    send_file(@file_png,
              {:filename => "#{@gid}-#{@sid}.png",
                :type => "application/png"})
  end

  private

  def sendmail(file_name)
    from = 'faxocr@localhost'
    to = 'faxocr@localhost'
    subject = 'Debug Mode'
    body = 'everynet.jp'
    host = "localhost"
    port = 25

    body = <<EOT
From: #{from}
To: #{to.to_a.join(",\n ")}
Subject: #{NKF.nkf("-WMm0", subject)}
Date: #{Time::now.strftime("%a, %d %b %Y %X %z")}
Mime-Version: 1.0
Content-Type: multipart/mixed; boundary="boundary.faxocr.debug"
X-MPlus-MsgType: 1
X-MPlus-MsgNo: 347601
X-MPlus-ReceiverUserID: 90016528
X-MPlus-CallerTelNo: 05000000000
X-MPlus-UniDTWhenThisWasSent: 1395122105

--boundary.faxocr.debug
Content-Type: text/plain; charset=ISO-2022-JP
Content-Transfer-Encoding: 7bit

#{NKF.nkf("-Wjm0", body)}

--boundary.faxocr.debug
Content-Type: application/octet-stream; name="#{File.basename(file_name)}"
Content-Disposition: attachment; name="#{File.basename(file_name)}"; filename="#{File.basename(file_name)}"
Content-Transfer-Encoding: base64

#{[File.open(file_name).readlines.join('')].pack('m')}
--boundary.faxocr.debug--
EOT
 
    Net::SMTP.start(host, port) do |smtp|
      smtp.send_mail body, from, to
    end
  end

end
