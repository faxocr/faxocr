class Candidate < ActiveRecord::Base
  belongs_to  :group
  has_many    :survey_candidates
  has_many    :surveys, :through => :survey_candidates

  has_many    :answer_sheets

  validates_presence_of :candidate_code, :candidate_name, :fax_number
  validates_uniqueness_of :candidate_code

end
