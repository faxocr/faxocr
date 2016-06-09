# -*- coding: utf-8 -*-

require 'rexml/text'

class Survey < ActiveRecord::Base
  belongs_to  :group

  has_many    :sheets, :dependent => :destroy
  has_many    :survey_properties, :dependent => :destroy

  has_many    :survey_candidates, :dependent => :destroy
  has_many    :candidates, :through => :survey_candidates

  STATUS = [["クローズ", 0], ["オープン", 1]]
  REPORT_WDAY = [['日', '0'], ['月', '1'], ['火', '2'], ['水', '3'], ['木', '4'], ['金', '5'], ['土', '6']]

  def printable_report_wday
    report_wday = self.report_wday
    string = ""
    if /0/ =~ report_wday
      string += "[日]"
    end
    if /1/ =~ report_wday
      string += "[月]"
    end
    if /2/ =~ report_wday
      string += "[火]"
    end
    if /3/ =~ report_wday
      string += "[水]"
    end
    if /4/ =~ report_wday
      string += "[木]"
    end
    if /5/ =~ report_wday
      string += "[金]"
    end
    if /6/ =~ report_wday
      string += "[土]"
    end
    return string
  end

  def has_report_wday(wday)
    report_wday = self.report_wday
    if /#{wday}/ =~ report_wday
      return true
    else
      return false
    end      
  end
  
  def has_report(hour, minute)
    report_time = self.report_time.strftime("%H").to_i * 60 + self.report_time.strftime("%M").to_i
    in_time = hour.to_i * 60 + minute.to_i
    if in_time >= report_time && in_time < (report_time + 15)
      return true
    else
      return false
    end
  end

#  validates_presence_of :survey_code, :survey_name, :group_id
#  validates_uniqueness_of :survey_code

  def sheet_ids
    sheet_ids = []
    sheets = Sheet.where(:survey_id => self.id).to_a
    sheets.each do |sheet|
      sheet_ids << sheet.id
    end
    return sheet_ids
  end

  def report_wday_by_array
    wday = self.report_wday
    if wday && wday.length > 0
      return wday.split(//)
    else
      return []
    end
  end

  def report_wday_by_array=(report_wdays_array)
    if report_wdays_array
      self.report_wday = report_wdays_array.join
    end
  end
  
  def get_srml(accept_sheet_statuses)
    srmlstr = ""
    sheets = self.sheets.where(:status => accept_sheet_statuses)
    if sheets != nil
      sheets.each do |sheet|
        srmlstr = srmlstr + sheet.get_one_srml_entry
      end
    end
    return srmlstr
  end
end
