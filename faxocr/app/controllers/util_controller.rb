class UtilController < ApplicationController
  def survey_fax_numbers
    survey = Survey.find_by_survey_code(params[:survey_code])
    @candidates = survey.candidates
  end

  def srml
    @sheet = Sheet.find_by_sheet_code(params[:sheet_code])
  end
end
