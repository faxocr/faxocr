class UtilController < ApplicationController
  def survey_fax_numbers
    survey = Survey.find_by_survey_code(params[:survey_code])
    @candidates = survey.candidates
  end

  def srml
    @sheet = Sheet.find_by_sheet_code(params[:sheet_code])
  end

  def get_srml_contents
    @srml_contents = generate_srml_contents do
      accept_survey_statuses = []
      accept_survey_statuses << 1
      accept_sheet_statuses = []
      accept_sheet_statuses << 1
      groups = Group.all
      srmlstr = ""
      groups.each do |group|
        surveys = group.surveys.where(:status => accept_survey_statuses)
        if surveys != nil
          surveys.each do |survey|
            srmlstr += survey.get_srml(accept_sheet_statuses)
          end
        end
      end
      srmlstr
    end
    render :xml => @srml_contents
  end

  def get_srml_entries_for_a_survey
  end

  def get_one_srml_entry
    sheet = Sheet.find_by_sheet_code(params[:sheet_code])
    @srml_contents = sheet.get_one_srml_entry
    render :xml => @srml_contents
  end

  def generate_srml_contents(&cb)
    srmlstr = "<srMl>\n"
    srmlstr = srmlstr + cb.call()
    srmlstr = srmlstr + "</srMl>\n"
    srmlstr
  end

end
