class InboxController < ApplicationController
  before_filter :verify_group_authority,
    :only => :group_surveys
  before_filter :verify_survey_authority,
    :only => :survey_answer_sheets
  before_filter :verify_answer_sheet_authority,
    :only => [:answer_sheet_properties, :update_answer_sheet_properties]

  def index
    redirect_to :controller => 'inbox', :action => :group_surveys, :group_id => @current_group.id
  end

  def group_surveys
    @surveys = @authorized_group.surveys
  end

  def survey_answer_sheets
    sheet_ids = []
    @authorized_survey.sheets.each {|sheet| sheet_ids << sheet.id}
    @answer_sheets = AnswerSheet.where(:sheet_id => sheet_ids).order(date: :desc)
  end

  def answer_sheet_properties
    @answer_sheet_properties = AnswerSheetProperty.select("asp.*").
      joins("AS asp INNER JOIN survey_properties AS sp ON asp.ocr_name = sp.ocr_name").
      where("asp.answer_sheet_id = ? AND sp.survey_id = ?", @authorized_answer_sheet.id, @authorized_survey.id).
      order("sp.view_order")
  end

  def update_answer_sheet_properties
    #@authorized_answer_sheet.update_attributes(params[:answer_sheet])
    @ocr_values = params[:ocr_values]
    @ocr_values.each do |id, ocr_value|
      answer_sheet_property = AnswerSheetProperty.find(id.to_i)
      if answer_sheet_property && answer_sheet_property.answer_sheet_id == @authorized_answer_sheet.id
        answer_sheet_property.update_attribute(:ocr_value, ocr_value)
      else
        access_violation
      end
    end
    redirect_to(:back)
  end
end
