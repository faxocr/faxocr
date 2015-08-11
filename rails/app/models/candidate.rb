class Candidate < ActiveRecord::Base
  belongs_to  :group
  has_many    :survey_candidates, :dependent => :destroy
  has_many    :surveys, :through => :survey_candidates

  has_many    :answer_sheets, :dependent => :destroy

  validates_presence_of :candidate_code, :candidate_name, :fax_number
  validates_uniqueness_of :candidate_code
  validates_format_of :candidate_code, :with => /\A\d{5}\z/
end
