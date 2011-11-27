# -*- coding: utf-8 -*-
class ExternalController < ApplicationController

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
    @html = `cd ./app/external; php form-overlay.php`

    render :dummy
  end

  # http://server_addr:3000/external/register/1234
  def register
    @gid = params[:group_id]
    @html = "GID: " + @gid + "\n<BR>"
    render :dummy
  end

  # http://server_addr:3000/external/form/1234/5678
  def form

    # 生成したpdfは、public/external/pdfに入れる方針で
    @gid = params[:group_id]
    @sid = params[:survey_id]
    @html = "GID: " + @gid + "\n<BR>"
    @html += "SID: " + @sid + "\n<BR>"
    render :dummy
  end

end
