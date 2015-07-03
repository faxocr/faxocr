# -*- coding: utf-8 -*-
class AnswerSheet < ActiveRecord::Base
  has_many :answer_sheet_properties, :dependent => :destroy

  belongs_to :sheet
  belongs_to :candidate

  SRERROR = [["成功", 0],
    ["オープンしているシートがありません", 1],
    ["３つのパターンが見つかりません", 2],
    ["有効なシートIDが見つかりません", 3],
    ["画像ファイルを読めません", 4],
    ["シート定義ファイルが存在しません", 5],
    ["不明なエラーコード", 6],
    ["不明なエラーコード", 7],
    ["不明なエラーコード", 8]]

  def self.find_all_by_need_check(need_check)
    if need_check
      AnswerSheet.find(:all, :select => "a.*",
        :joins => "AS a INNER JOIN answer_sheet_properties AS ap ON a.id = ap.answer_sheet_id",
        :conditions => "ap.need_check = true",
        :group => "a.id")
    else
      AnswerSheet.find(:all, :select => "a.*",
        :joins => "AS a INNER JOIN answer_sheet_properties AS ap ON a.id = ap.answer_sheet_id",
        :group => "a.id",
        :having => "sum(ap.need_check) = 0")
    end
  end

  def needs_check?
    answer_sheet_properties = AnswerSheetProperty.find_all_by_answer_sheet_id_and_need_check(self.id, true)
    answer_sheet_properties.length == 0 ? false : true
  end
  
  def printable_srerror
    return SRERROR[self.srerror][0]
  end
  
  def candidate_code=(code)
    candidate_id = 1
    candidate = Candidate.find_by_candidate_code(code)
    if candidate != nil
      candidate_id = candidate.id
    end
    self.candidate_id = candidate_id
    self.analyzed_candidate_code = code
  end

  def candidate_code
    return self.analyzed_candidate_code
  end

  def sheet_code=(code)
    sheet_id = 1
    sheet = Sheet.find_by_sheet_code(code)
    if sheet != nil
      sheet_id = sheet.id
    end
    self.sheet_id = sheet_id
    self.analyzed_sheet_code = code
  end

  def sheet_code
    return self.analyzed_sheet_code
  end
  
  def rerecognize
    accept_sheet_statuses = []
    accept_sheet_statuses << 1
    accept_sheet_statuses << 2
    accept_sheet_statuses << 3
    tmpdir = "/tmp/rails_answer_sheet_#{self.id}"
    tmpdir_sr = "#{tmpdir}/sr"
    tmp_srml = "#{tmpdir}/sr/srml/faxocr.xml"
    system("rm -f #{tmpdir}")
    system("rm -Rf #{tmpdir}")
    system("mkdir #{tmpdir}")
    system("cp -R #{Rails.root}/faxocr_config/recognize_sheetreader #{tmpdir_sr}")

    srmlstr = "<srMl>\n"
    groups = Group.find(:all)
    groups.each do |group|
      surveys = group.surveys
      if surveys != nil
        surveys.each do |survey|
          srmlstr = srmlstr + survey.get_srml(accept_sheet_statuses)
        end
      end
    end
    srmlstr = srmlstr + "</srMl>\n"
    File.open(tmp_srml, 'w') {|f| f.write(srmlstr) }
    system("echo sheetreader -m rails -c #{tmpdir_sr} -u #{self.analyzed_candidate_code} -i #{self.analyzed_sheet_code} -r #{self.receiver_number} -s #{self.sender_number} -p #{MyAppConf::IMAGE_PATH_PREFIX}/ #{MyAppConf::IMAGE_PATH_PREFIX}/#{self.sheet_image} 1> #{tmpdir}/result")
    system("sheetreader -m rails -c #{tmpdir_sr} -u #{self.analyzed_candidate_code} -i #{self.analyzed_sheet_code} -r #{self.receiver_number} -s #{self.sender_number} -p #{MyAppConf::IMAGE_PATH_PREFIX} #{MyAppConf::IMAGE_PATH_PREFIX}#{self.sheet_image} 1> #{tmpdir}/result.rb 2>#{tmpdir}/result.err")
    system("ruby #{tmpdir}/result.rb #{Rails.root} #{MyAppConf::IMAGE_PATH_PREFIX} #{tmpdir}/echoresult.html")
  end
end
