# -*- coding: utf-8 -*-
class ExportController < ApplicationController
  before_filter :verify_survey_authority
  def csv
    year = params[:year]
    month = params[:month]
    day = params[:day]
    if year == nil
      date_range = "%-%-% %:%:%"
    elsif month == nil
      date_range = "#{year}-%-% %:%:%"
    elsif day == nil
      date_range = "#{year}-#{month}-% %:%:%"
    else
      date_range = "#{year}-#{month}-#{day} %:%:%"
    end
    @survey = Survey.find(params[:survey_id])
    unless @survey
      # No survey found (@survey = nil)
      render :template => 'report/blank'
      return
    end

    # Survey may have multiple sheets
    @survey_candidates = @survey.survey_candidates
    candidate_ids = []
    @survey_candidates.each do |survey_candidate|
      if survey_candidate.has_receivereport_role
        candidate_ids << survey_candidate.candidate_id
      end
    end
    sheet_ids = @survey.sheet_ids
    @answer_sheets = AnswerSheet.where(:sheet_id => sheet_ids).where(:candidate_id => candidate_ids).where('date like ?', date_range).order(date: :asc)
    #Makes the header of csv.
    survey_properties = SurveyProperty.where(:survey_id => @survey.id).order(view_order: :asc)
    csv_string = "日付,調査対象,電話番号"
    columnames = []
    survey_properties.each do |rp|
      columnames << rp.ocr_name
      csv_string = "#{csv_string},#{rp.ocr_name_full}"
    end
    csv_string = "#{csv_string}\r\n"
    #Makes data.
    @answer_sheets.each do |a|
      line_string = nil
      for columname in columnames do
        rp = AnswerSheetProperty.find_by_answer_sheet_id_and_ocr_name(a.id, columname)
        line_string = (rp == nil) ? "#{line_string}," : "#{line_string},#{rp.ocr_value}"
      end
      if line_string != nil
        csv_string = csv_string + 
          "#{a.date.to_s(:date_nomal)},#{a.candidate.candidate_name},#{a.candidate.tel_number}" + 
          line_string + "\r\n"
      end
    end
    disposition_string = "inline; filename=\"#{year}#{month}#{day}_#{@survey.survey_name}.csv\""
    if request.user_agent =~ /windows/i
      #Puts csv in Shift-JIS.
      csv_string.encode!("SJIS")
      disposition_string.encode!("SJIS")
    end
    response.headers['Content-Type'] = 'text/csv'
    response.headers['Content-Disposition'] = disposition_string
    render :text => csv_string, :layout => false
  end

end
