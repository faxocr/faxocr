class SheetProperty < ActiveRecord::Base
  belongs_to :survey_property
  belongs_to :sheet
end
