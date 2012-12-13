class SheetProperty < ActiveRecord::Base
  belongs_to :survey_property
  belongs_to :sheet

  validates_presence_of :survey_property_id
end
