class ReportController < ApplicationController
  before_filter :verify_survey_authority
  before_filter :report_options

  def daily
    year = params[:year]
    month = params[:month]
    day = params[:day]
    date_begin = "#{year}/#{month}/#{day} 00:00:00"
    date_end =  "#{year}/#{month}/#{day} 23:59:59"
    
    @group = Group.find(params[:group_id])
    @survey = Survey.find(params[:survey_id])
    unless @survey
      # No survey found (@survey = nil)
      render :template => 'report/blank'
      return
    end

    # Survey may have multiple sheets
    @survey_candidates = @survey.survey_candidates
    sheet_ids = @survey.sheet_ids

    @answer_sheets = []
    @survey_candidates.each do |survey_candidate|
      if survey_candidate.has_receivereport_role
        @answer_sheet = AnswerSheet.where(:sheet_id => sheet_ids).where(:candidate_id => survey_candidate.candidate_id).where('date >= ? and date <= ?', date_begin, date_end).order(date: :desc).take
        if @answer_sheet == nil
          @answer_sheet = AnswerSheet.new
          @answer_sheet.candidate_id = survey_candidate.candidate_id
        end
        @answer_sheets << @answer_sheet
      end
    end
    @place_holder = Hash.new
    @place_holder.store('YEAR', year)
    @place_holder.store('MONTH', month)
    @place_holder.store('DAY', day)
    @prefix_image = "/images/ocr"
  end

  def fax_preview
    daily
    render :layout => false
  end

private
  def report_options
    if params.key?(:image)
      @all_image = true
    end
  end
end
