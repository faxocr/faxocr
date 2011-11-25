class SurveyProperty < ActiveRecord::Base
  belongs_to :survey
  DATA_TYPE = [["数字", "number"], ["○△×", "rating"], ["画像", "image"]]

  validates_uniqueness_of :ocr_name, :scope => "survey_id"

  def printable_data_type
    data_type = self.data_type
    string = ""
    if /number/ =~ data_type
      string += "数字"
    end
    if /rating/ =~ data_type
      string += "○△×"
    end
    if /image/ =~ data_type
      string += "画像"
    end
    return string
  end
end
