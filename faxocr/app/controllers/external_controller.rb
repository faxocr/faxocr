# -*- coding: utf-8 -*-
class ExternalController < ApplicationController

  skip_before_filter :verify_authenticity_token ,:only => [:reg_upload, :reg_exec, :sht_field, :sht_script, :sht_marker, :sht_config, :sht_verify, :sht_commit]

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
    @html += `cd ./app/external; php contrib/test.php`
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
    @html += "RAILS_ENV=#{RAILS_ENV}"
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
    @action = "/external/reg_upload/"
  end

  def reg_upload

    @gid = params[:gid]
    if params[:file].nil? then
      redirect_to :controller => 'external', :action => 'register', :id => @gid
      return
    else
      @filename = params[:file]['upfile'].original_filename
      @size = params[:file]['upfile'].size
      @tname = @gid + "-" + Time.now().strftime("%Y%m%d%H%M")
    end

    if 0 < @size && @size <= @@size_limit && /\.xls$/ =~ @filename then
      File.open("#{RAILS_ROOT}/files/#{@tname}" + ".lst", "wb") {
        |f| f.write(params[:file]['upfile'].read)
      }
      # @html = "GID: " + @gid + "\n<BR>" +
      #  "Filename: " + @filename + " (" + @size.to_s + ")\n<BR>" +
      #  "Temp file: " + @tname
      # @html = `cd ./app/external; php reg_upload.php`
      # @html = `echo php reg_upload.php file=\"#{@tname}\"`
      @html = `cd ./app/external; php reg_upload.php gid=\"#{@gid}\" file=\"#{@tname}\" rails_env=\"#{RAILS_ENV}\"`
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
    @result = `ruby #{RAILS_ROOT}/files/#{@file}.rb #{RAILS_ROOT} #{@gid}`

    flash[:notice] = "調査対象を一括登録しました"
    redirect_to group_candidates_url(@group)
  end

  # http://server_addr:3000/external/sheet/1234/5678
  def sheet

    # 生成したpdfは、public/external/pdfに入れる方針で
    # @gid = params[:group_id]
    @gid = params[:group_id].nil? ? params[:id] : params[:group_id]
    @sid = params[:survey_id]

    @limit = (@@size_limit / 1024).to_s + "K"

    @action = "/external/sht_field/"

    # @html = "GID: " + @gid + "\n<BR>"
    # @html += "SID: " + @sid + "\n<BR>"
    # render :dummy
  end

  def sht_field

    @gid = params[:gid]
    @sid = params[:sid]
    @tname = params[:file_id]

    @msg = flash[:notice]

    if not @tname.nil? then
      @html = `cd ./app/external; php sht_field.php gid=\"#{@gid}\" sid=\"#{@sid}\" file=\"#{@tname}\" msg=\"#{@msg}\"`
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

    if 0 < @size && @size <= @@size_limit && /\.xls$/ =~ @filename then
      File.open("#{RAILS_ROOT}/files/#{@tname}" + ".xls", "wb") {
        |f| f.write(params[:file]['upfile'].read)
      }
      # @html = "GID: " + @gid + "\n<BR>" +
      #  "Filename: " + @filename + " (" + @size.to_s + ")\n<BR>" +
      #  "Temp file: " + @tname
      # @html = `cd ./app/external; php reg_upload.php`
      # @html = `echo php reg_upload.php file=\"#{@tname}\"`
      @html = `cd ./app/external; php sht_field.php gid=\"#{@gid}\" sid=\"#{@sid}\" file=\"#{@tname}\"`
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

    @param_str = ""
    params.each {|key, value|
      @param_str += key + "=\"" + value + "\" "
    }
    # @errmsg = `cd ./app/external; php sht_script.php #{@param_str}`
    # flash[:notice] = @errmsg
    # flash[:notice] = "セーブしました"

    # @html = "<PRE>\n"
    # @html += @param_str + "\n"
    # @html += "<PRE>\n"
    # render :dummy

    redirect_to :controller => 'external', :action => 'sht_field', :params => {:gid => @gid, :sid => @sid, :file_id => @fileid}
  end

  def sht_marker

    @gid = params[:gid]
    @sid = params[:sid]
    @file = params[:fileid]
    @msg = flash[:notice]

    @file = @file.nil? ? params[:file_id] : @file
    # @group = Group.find(params[:gid])

    @html = `cd ./app/external; php sht_marker.php gid=\"#{@gid}\" sid=\"#{@sid}\" file=\"#{@file}\" msg=\"#{@msg}\"`
    render :dummy
  end

  def sht_config

    @gid = params[:gid]
    @sid = params[:sid]
    @file = params[:fileid]

    @param_str = ""
    params.each {|key, value|
      @param_str += key + "=\"" + value + "\" "
    }

    @ret = `cd ./app/external; php sht_config.php file=\"#{@file}\" #{@param_str}`
    # flash[:notice] = @errmsg
    # flash[:notice] = "セーブしました"

    # @html = "<PRE>\n"
    # @html += @param_str + "\n"
    # @html += "<PRE>\n"
    # @html += @ret
    # render :dummy

    @file_html = "#{RAILS_ROOT}/files/#{@file}.html"
    @file_pdf = "#{RAILS_ROOT}/files/#{@gid}-#{@sid}.pdf"
    @file_png = "#{RAILS_ROOT}/files/#{@gid}-#{@sid}.png"

    # @ret = `cd #{RAILS_ROOT}; xvfb-run -a wkhtmltopdf --page-size A4 --orientation Landscape #{@file_html} #{@file_pdf}`
    @ret = `cd #{RAILS_ROOT}; xvfb-run -a wkhtmltopdf --page-size A4 --orientation Landscape #{@file_html} #{@file_pdf}`
    @ret = `convert #{@file_pdf} #{@file_png}`

    redirect_to :controller => 'external', :action => 'sht_marker', :params => {:gid => @gid, :sid => @sid, :file_id => @file}
  end

  def sht_verify

    @gid = params[:gid]
    @sid = params[:sid]
    @file = params[:fileid]
    @msg = flash[:notice]

    @html = `cd ./app/external; php sht_verify.php gid=\"#{@gid}\" sid=\"#{@sid}\" file=\"#{@file}\" msg=\"#{@msg}\"`
    render :dummy
  end

  def sht_commit

    @gid = params[:gid]
    @sid = params[:sid]
    @file = params[:file]
    @group = Group.find(params[:gid])

    # @result = `ruby #{RAILS_ROOT}/files/#{@file}.rb #{RAILS_ROOT} #{@gid}`

    flash[:notice] = "シートを登録しました"
    redirect_to group_url(@group)
  end

  def download

    @gid = params[:group_id].nil? ? params[:id] : params[:group_id]
    @sid = params[:survey_id]
    @file_pdf = "#{RAILS_ROOT}/files/#{@gid}-#{@sid}.pdf"

    # send_file("#{RAILS_ROOT}/files/simple.zip",
    #          {:filename => "simple.zip",
    #           :type => "application/zip"})

    # send_file("#{RAILS_ROOT}/files/simple.pdf",
    #           {:filename => "simple.pdf",
    #             :type => "application/pdf"})

    send_file(@file_pdf,
              {:filename => "#{@gid}-#{@sid}.pdf",
                :type => "application/pdf"})
  end

  def getimg

    @gid = params[:group_id]
    @sid = params[:survey_id]
    @file_png = "#{RAILS_ROOT}/files/#{@gid}-#{@sid}.png"

    send_file(@file_png,
              {:filename => "#{@gid}-#{@sid}.png",
                :type => "application/png"})
  end

end
