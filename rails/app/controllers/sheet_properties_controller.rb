# -*- coding: utf-8 -*-

class SheetPropertiesController < ApplicationController
  before_filter :verify_group_authority
  # GET /sheet_properties
  # GET /sheet_properties.xml
  def index
    @group = Group.find(params[:group_id])
    @survey = @group.surveys.find(params[:survey_id])
    @sheet = @survey.sheets.find(params[:sheet_id])
    @sheet_properties = @sheet.sheet_properties
    respond_to do |format|
      format.html # index.html.erb
      format.xml  { render :xml => @sheet_properties }
    end
  end

  # GET /sheet_properties/1
  # GET /sheet_properties/1.xml
  def show
    @group = Group.find(params[:group_id])
    @survey = @group.surveys.find(params[:survey_id])
    @sheet = @survey.sheets.find(params[:sheet_id])
    @sheet_property = @sheet.sheet_properties.find(params[:id])
    respond_to do |format|
      format.html # show.html.erb
      format.xml  { render :xml => @sheet_property }
    end
  end

  # GET /sheet_properties/new
  # GET /sheet_properties/new.xml
  def new
    @group = Group.find(params[:group_id])
    @survey = @group.surveys.find(params[:survey_id])
    @sheet = @survey.sheets.find(params[:sheet_id])
    @sheet_property = @sheet.sheet_properties.build
    respond_to do |format|
      format.html # new.html.erb
      format.xml  { render :xml => @sheet_property }
    end
  end

  # GET /sheet_properties/1/edit
  def edit
    @group = Group.find(params[:group_id])
    @survey = @group.surveys.find(params[:survey_id])
    @sheet = @survey.sheets.find(params[:sheet_id])
    @sheet_property = @sheet.sheet_properties.find(params[:id])
    respond_to do |format|
      format.html # index.html.erb
      format.xml  { render :xml => @sheet_property }
    end
  end

  # POST /sheet_properties
  # POST /sheet_properties.xml
  def create
    @group = Group.find(params[:group_id])
    @survey = @group.surveys.find(params[:survey_id])
    @sheet = @survey.sheets.find(params[:sheet_id])
    @sheet_property = @sheet.sheet_properties.build(sheet_property_params)
    if @sheet_property.save
      redirect_to group_survey_sheet_sheet_property_url(@group, @survey, @sheet, @sheet_property)
    else
      render :action => "new"
    end
  end

  # PUT /sheet_properties/1
  # PUT /sheet_properties/1.xml
  def update
    @group = Group.find(params[:group_id])
    @survey = Survey.find(params[:survey_id])
    @sheet = Sheet.find(params[:sheet_id])
    @sheet_property = SheetProperty.find(params[:id])
    if @sheet_property.update_attributes(sheet_property_params)
      redirect_to group_survey_sheet_sheet_property_url(@group, @survey, @sheet, @sheet_property)
    else
      render :action => "edit"
    end
  end

  # DELETE /sheet_properties/1
  # DELETE /sheet_properties/1.xml
  def destroy
    @group = Group.find(params[:group_id])
    @survey = Survey.find(params[:survey_id])
    @sheet = Sheet.find(params[:sheet_id])
    @sheet_property = SheetProperty.find(params[:id])
    sheet_ids = @survey.sheet_ids
    answer_sheet = AnswerSheet.find_by_sheet_id(sheet_ids)
    if answer_sheet != nil
      answer_sheet_property = AnswerSheetProperty.find_by_answer_sheet_id_and_ocr_name(answer_sheet.id, @sheet_property.ocr_name)
    else
      answer_sheet_property = nil      
    end
    if answer_sheet_property != nil
      flash[:notice] = "この調査項目は使用されているため削除できません"
    else
      @sheet_property.destroy
    end
    respond_to do |format|
      format.html { redirect_to group_survey_sheet_sheet_properties_path(@group, @survey, @sheet) }
      format.xml  { head :ok }
    end
  end

  private

  def sheet_property_params
    params.require(:sheet_property).permit(:sheet_id, :survey_property_id, :position_x, :position_y, :colspan)
  end

end
