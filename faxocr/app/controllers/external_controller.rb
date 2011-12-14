# -*- coding: utf-8 -*-
class ExternalController < ApplicationController

  skip_before_filter :verify_authenticity_token ,:only => [:reg_upload, :reg_exec,:sht_field,:sht_marker,:sht_verify,:sht_commit]

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
    if params[:file].nil? then
      redirect_to :controller => 'external', :action => 'sheet', :group_id => @gid, :survey_id => @sid
      return
    else
      @filename = params[:file]['upfile'].original_filename
      @size = params[:file]['upfile'].size
      @tname = @gid + "-" + Time.now().strftime("%Y%m%d%H%M")
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

  def sht_marker

    @gid = params[:gid]
    @sid = params[:sid]
    @file = params[:file]
    # @group = Group.find(params[:gid])

    @html = `cd ./app/external; php sht_marker.php gid=\"#{@gid}\" sid=\"#{@sid}\" file=\"#{@file}\"`
    render :dummy
  end

  def sht_verify

    @gid = params[:gid]
    @sid = params[:sid]
    @file = params[:file]
    # @group = Group.find(params[:gid])

    @html = `cd ./app/external; php sht_verify.php gid=\"#{@gid}\" sid=\"#{@sid}\" file=\"#{@file}\"`
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

    send_file("#{RAILS_ROOT}/files/simple.zip",
              {:filename => "simple.zip",
                :type => "application/zip"})

    # send_file("#{RAILS_ROOT}/files/simple.pdf",
    #           {:filename => "simple.pdf",
    #             :type => "application/pdf"})

    # send_file("#{RAILS_ROOT}/files/#{@file}.xls",
    #           {:filename => @folder.filename,
    #            :type => @folder.content_type})
  end

end
