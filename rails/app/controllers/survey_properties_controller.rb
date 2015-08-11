# -*- coding: utf-8 -*-
class SurveyPropertiesController < ApplicationController
  # GET /survey_properties
  # GET /survey_properties.xml
  def index
    @group = Group.find(params[:group_id])
    @survey = @group.surveys.find(params[:survey_id])
    @survey_properties = @survey.survey_properties.all(:order => "view_order")
    respond_to do |format|
      format.html # index.html.erb
      format.xml  { render :xml => @survey_properties }
    end
  end

  # GET /survey_properties/1
  # GET /survey_properties/1.xml
  def show
    @group = Group.find(params[:group_id])
    @survey = @group.surveys.find(params[:survey_id])
    @survey_property = @survey.survey_properties.find(params[:id])
    respond_to do |format|
      format.html # show.html.erb
      format.xml  { render :xml => @survey_property }
    end
  end

  # GET /survey_properties/new
  # GET /survey_properties/new.xml
  def new
    @group = Group.find(params[:group_id])
    @survey = @group.surveys.find(params[:survey_id])
    @survey_property = @survey.survey_properties.build
    respond_to do |format|
      format.html # new.html.erb
      format.xml  { render :xml => @survey_property }
    end
  end

  # GET /survey_properties/1/edit
  def edit
    @group = Group.find(params[:group_id])
    @survey = @group.surveys.find(params[:survey_id])
    @survey_property = @survey.survey_properties.find(params[:id])
    respond_to do |format|
      format.html # index.html.erb
      format.xml  { render :xml => @survey_property }
    end
  end

  # POST /survey_properties
  # POST /survey_properties.xml
  def create
    @group = Group.find(params[:group_id])
    @survey = @group.surveys.find(params[:survey_id])
    @survey_property = @survey.survey_properties.build(survey_property_params)
    if @survey_property.save
      redirect_to group_survey_survey_properties_url(@group, @survey)
    else
      render :action => "new"
    end
  end

  # PUT /survey_properties/1
  # PUT /survey_properties/1.xml
  def update
    @group = Group.find(params[:group_id])
    @survey = Survey.find(params[:survey_id])
    @survey_property = SurveyProperty.find(params[:id])
    if @survey_property.update_attributes(survey_property_params)
      redirect_to group_survey_survey_property_url(@group, @survey, @survey_property)
    else
      render :action => "edit"
    end
  end

  # DELETE /survey_properties/1
  # DELETE /survey_properties/1.xml
  def destroy
    @group = Group.find(params[:group_id])
    @survey = Survey.find(params[:survey_id])
    @survey_property = SurveyProperty.find(params[:id])
    sheet_property = SheetProperty.find_by_survey_property_id(params[:id])
    if sheet_property != nil
      flash[:notice] = "この調査項目は使用されているため削除できません"
    else
      @survey_property.destroy
    end
    respond_to do |format|
      format.html { redirect_to group_survey_survey_properties_path(@group, @survey) }
      format.xml  { head :ok }
    end
  end

  private

  def survey_property_params
    params.require(:survey_property).permit(:survey_id, :ocr_name_full, :ocr_name, :view_order, :data_type)
  end
end
