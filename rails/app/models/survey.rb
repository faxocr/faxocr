# -*- coding: utf-8 -*-
class Survey < ActiveRecord::Base
  belongs_to  :group

  has_many    :sheets
  has_many    :survey_properties

  has_many    :survey_candidates
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
    sheets = Sheet.find_all_by_survey_id(self.id)
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
    sheets = self.sheets.find_all_by_status(accept_sheet_statuses)
    if sheets != nil
      sheets.each do |sheet|
        srmlstr = srmlstr + "  <sheet>\n"
        srmlstr = srmlstr + "    <id>#{sheet.sheet_code}</id>\n"
        srmlstr = srmlstr + "    <blockWidth>#{sheet.block_width -1}</blockWidth>\n"
        srmlstr = srmlstr + "    <blockHeight>#{sheet.block_height -1}</blockHeight>\n"
        srmlstr = srmlstr + "    <cellWidth>\n"
        eval(sheet.cell_width).sort.each do |cell_no,cell_width|
          srmlstr = srmlstr + "      <cellAttribute number=\"#{cell_no}\" length=\"#{cell_width}\"/>\n"
        end
        srmlstr = srmlstr + "    </cellWidth>\n"
        srmlstr = srmlstr + "    <cellHeight>\n"
        eval(sheet.cell_height).sort.each do |cell_no,cell_height|
          srmlstr = srmlstr + "      <cellAttribute number=\"#{cell_no}\" length=\"#{cell_height}\"/>\n"
        end
        srmlstr = srmlstr + "    </cellHeight>\n"
        srmlstr = srmlstr + "    <properties>\n"
        srmlstr = srmlstr + "      <blockOcr"
        srmlstr = srmlstr + " name=\"echo_request_and_send_analyzed_data\""
        srmlstr = srmlstr + " x=\"#{sheet.block_width -1}\""
        srmlstr = srmlstr + " y=\"#{sheet.block_height -1}\""
        srmlstr = srmlstr + " colspan=\"1\""
        srmlstr = srmlstr + " option=\"rating\""
        srmlstr = srmlstr + "/>\n"
        if sheet.sheet_properties != nil
          sheet.sheet_properties.each do |property|
            if property.survey_property.data_type == "image"
              srmlstr = srmlstr + "      <blockImg"
              srmlstr = srmlstr + " name=\"#{property.survey_property.ocr_name}\""
              srmlstr = srmlstr + " x=\"#{property.position_x}\""
              srmlstr = srmlstr + " y=\"#{property.position_y}\""
              srmlstr = srmlstr + " colspan=\"#{property.colspan}\""
              srmlstr = srmlstr + " margin=\"15\""
              srmlstr = srmlstr + "/>\n"                
            else
              srmlstr = srmlstr + "      <blockOcr"
              srmlstr = srmlstr + " name=\"#{property.survey_property.ocr_name}\""
              srmlstr = srmlstr + " x=\"#{property.position_x}\""
              srmlstr = srmlstr + " y=\"#{property.position_y}\""
              srmlstr = srmlstr + " colspan=\"#{property.colspan}\""
              srmlstr = srmlstr + " option=\"#{property.survey_property.data_type}\""
              srmlstr = srmlstr + "/>\n"
              srmlstr = srmlstr + "      <blockImg"
              srmlstr = srmlstr + " name=\"#{property.survey_property.ocr_name}\""
              srmlstr = srmlstr + " x=\"#{property.position_x}\""
              srmlstr = srmlstr + " y=\"#{property.position_y}\""
              srmlstr = srmlstr + " colspan=\"#{property.colspan}\""
              srmlstr = srmlstr + " margin=\"15\""
              srmlstr = srmlstr + "/>\n"
            end
          end
        end
        srmlstr = srmlstr + "    </properties>\n"
        srmlstr = srmlstr + "  </sheet>\n"
      end
    end
    return srmlstr
  end
end
