# -*- coding: utf-8 -*-
class SheetsController < ApplicationController
  before_filter :verify_group_authority
  # GET /sheets
  # GET /sheets.xml
  def index
    @group = Group.find(params[:group_id])
    @survey = @group.surveys.find(params[:survey_id])
    @sheets = @survey.sheets
    respond_to do |format|
      format.html # index.html.erb
      format.xml  { render :xml => @sheets }
    end
  end

  # GET /sheets/1
  # GET /sheets/1.xml
  def show
    @group = Group.find(params[:group_id])
    @survey = @group.surveys.find(params[:survey_id])
    @sheet = @survey.sheets.find(params[:id])
    @sheet_properties = @sheet.sheet_properties 
    respond_to do |format|
      format.html # show.html.erb
      format.xml  { render :xml => @sheet }
    end
  end

  # GET /sheets/new
  # GET /sheets/new.xml
  def new
    @group = Group.find(params[:group_id])
    @survey = @group.surveys.find(params[:survey_id])
    @sheet = @survey.sheets.build
    respond_to do |format|
      format.html # new.html.erb
      format.xml  { render :xml => @sheet }
    end
  end

  # GET /sheets/1/edit
  def edit
    @group = Group.find(params[:group_id])
    @survey = @group.surveys.find(params[:survey_id])
    @sheet = @survey.sheets.find(params[:id])
    respond_to do |format|
      format.html # index.html.erb
      format.xml  { render :xml => @sheet }
    end
  end

  # POST /sheets
  # POST /sheets.xml
  def create
    @group = Group.find(params[:group_id])
    @survey = @group.surveys.find(params[:survey_id])
    @sheet = @survey.sheets.build(sheet_params)
      
    survey_properties = @survey.survey_properties
    survey_properties.each do |survey_property|
      sheet_property = SheetProperty.new
      sheet_property.position_x = 1
      sheet_property.position_y = 1
      sheet_property.colspan = 1
      sheet_property.survey_property_id = survey_property.id
      @sheet.sheet_properties << sheet_property
    end

    if @sheet.save
      redirect_to group_survey_sheet_url(@group, @survey, @sheet)
    else
      render :action => "new"
    end
  end

  # PUT /sheets/1
  # PUT /sheets/1.xml
  def update
    @group = Group.find(params[:group_id])
    @survey = Survey.find(params[:survey_id])
    @sheet = Sheet.find(params[:id])
    if @sheet.update_attributes(sheet_params)
      redirect_to group_survey_sheet_url(@group, @survey, @sheet)
    else
      render :action => "edit"
    end
  end

  # DELETE /sheets/1
  # DELETE /sheets/1.xml
  def destroy
    @group = Group.find(params[:group_id])
    @survey = Survey.find(params[:survey_id])
    @sheet = Sheet.find(params[:id])
    @sheet.destroy

    respond_to do |format|
      format.html { redirect_to group_survey_sheets_path(@group, @survey) }
      format.xml  { head :ok }
    end
  end

  private

  def sheet_params
    params.require(:sheet).permit(:survey_id, :sheet_name, :status, :sheet_code, :block_width, :block_height)
  end
end
