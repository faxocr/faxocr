# -*- coding: undecided -*-
class AnswerSheetsController < ApplicationController
  before_filter :verify_group_authority
  # GET /answer_sheets
  # GET /answer_sheets.xml
  def index
    @group = Group.find(params[:group_id])
    @survey = @group.surveys.find(params[:survey_id])
    sheet_ids = @survey.sheet_ids
    @answer_sheets = AnswerSheet.find_all_by_sheet_id(sheet_ids,
        :order => 'date desc')
    respond_to do |format|
      format.html # index.html.erb
      format.xml  { render :xml => @answer_sheets }
    end
  end

  # GET /answer_sheets/1
  # GET /answer_sheets/1.xml
  def show
    @group = Group.find(params[:group_id])
    @survey = @group.surveys.find(params[:survey_id])
    begin
    @answer_sheet = AnswerSheet.find(params[:id])
    @answer_sheet_properties = @answer_sheet.answer_sheet_properties
    sheets = @survey.sheets
    sheet = sheets.find(@answer_sheet.sheet_id)
    rescue
      sheet = nil
    end
    if sheet == nil
      redirect_to(group_survey_answer_sheets_url(@group, @survey))
    else
      respond_to do |format|
        format.html # show.html.erb
        format.xml  { render :xml => @answer_sheet }
      end
    end
  end

  def image
    @answer_sheet = AnswerSheet.find(params[:id])
    @answer_sheet_properties = @answer_sheet.answer_sheet_properties
    @group = Group.find(params[:group_id])
    @survey = @group.surveys.find(params[:survey_id])
    sheets = @survey.sheets
    sheet = sheets.find(@answer_sheet.sheet_id)
    if sheet == nil
      redirect_to(group_survey_answer_sheets_url(@group, @survey))
    end
    response.headers['Content-Type'] = 'image/png'
    image = ""
    File.open("#{MyAppConf::IMAGE_PATH_PREFIX}#{@answer_sheet.sheet_image}").each do |buff|
      image = image + buff
    end
    render :text => image, :layout => false
  end

  # GET /answer_sheets/new
  # GET /answer_sheets/new.xml
  def new
    @answer_sheet = AnswerSheet.new

    respond_to do |format|
      format.html # new.html.erb
      format.xml  { render :xml => @answer_sheet }
    end
  end

  # GET /answer_sheets/1/edit
  def edit
    @answer_sheet = AnswerSheet.find(params[:id])
    @group = Group.find(params[:group_id])
    @survey = @group.surveys.find(params[:survey_id])
    sheets = @survey.sheets
    sheet = sheets.find(@answer_sheet.sheet_id)
    if sheet == nil
      redirect_to(group_survey_answer_sheets_url(@group, @survey))
    end
  end

  # GET /answer_sheets/1/edit
  def edit_recognize
    @answer_sheet = AnswerSheet.find(params[:id])
    @group = Group.find(params[:group_id])
    @survey = @group.surveys.find(params[:survey_id])
    sheets = @survey.sheets
    sheet = sheets.find(@answer_sheet.sheet_id)
    if sheet == nil
      redirect_to(group_survey_answer_sheets_url(@group, @survey))
    end
  end

  # POST /answer_sheets
  # POST /answer_sheets.xml
  def create
    @group = Group.find(params[:group_id])
    @survey = @group.surveys.find(params[:survey_id])
    @answer_sheet = AnswerSheet.new(params[:answer_sheet])

    respond_to do |format|
      if @answer_sheet.save
        flash[:notice] = '受信FAXを作成しました'
        format.html { redirect_to(@answer_sheet) }
        format.xml  { render :xml => @answer_sheet, :status => :created, :location => @answer_sheet }
      else
        format.html { render :action => "new" }
        format.xml  { render :xml => @answer_sheet.errors, :status => :unprocessable_entity }
      end
    end
  end

  # PUT /answer_sheets/1
  # PUT /answer_sheets/1.xml
  def update
    @answer_sheet = AnswerSheet.find(params[:id])
    @group = Group.find(params[:group_id])
    @survey = @group.surveys.find(params[:survey_id])
    if @answer_sheet.update_attributes(params[:answer_sheet])
      redirect_to group_survey_answer_sheet_url(@group, @survey, @answer_sheet)
    else
      render :action => "edit"
    end
  end

  def update_recognize
    @answer_sheet = AnswerSheet.find(params[:id])
    @group = Group.find(params[:group_id])
    @survey = @group.surveys.find(params[:survey_id])
    @answer_sheet.analyzed_sheet_code = params[:answer_sheet]["analyzed_sheet_code"]
    @answer_sheet.analyzed_candidate_code = params[:answer_sheet]["analyzed_candidate_code"]
    @answer_sheet.sender_number = params[:answer_sheet]["sender_number"]
    @answer_sheet.receiver_number = params[:answer_sheet]["receiver_number"]
    @answer_sheet.rerecognize
    redirect_to group_survey_answer_sheet_url(@group, @survey, @answer_sheet)
  end

  # DELETE /answer_sheets/1
  # DELETE /answer_sheets/1.xml
  def destroy
    @group = Group.find(params[:group_id])
    @survey = @group.surveys.find(params[:survey_id])

    @answer_sheet = AnswerSheet.find(params[:id])
    @answer_sheet_properties = @answer_sheet.answer_sheet_properties

    # deletes files in the file-system;
    @answer_sheet_properties.each do |answer_sheet_propertie|
      filename = MyAppConf::IMAGE_PATH_PREFIX + answer_sheet_propertie.ocr_image
      if File.exist?(filename) && File.ftype(filename) == "file"
          File.delete(MyAppConf::IMAGE_PATH_PREFIX + answer_sheet_propertie.ocr_image)
      end
    end
    filename = MyAppConf::IMAGE_PATH_PREFIX + @answer_sheet.sheet_image
    if File.exist?(filename) && File.ftype(filename) == "file"
      File.delete(MyAppConf::IMAGE_PATH_PREFIX + @answer_sheet.sheet_image)
    end
    # deletes records in the db;
    AnswerSheetProperty.destroy_all(["answer_sheet_id = ?", @answer_sheet.id])
    @answer_sheet.destroy

    respond_to do |format|
      format.html { redirect_to(group_survey_answer_sheets_url(@group, @survey)) }
      format.xml  { head :ok }
    end
  end
end
