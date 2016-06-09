# -*- coding: utf-8 -*-
class AnswerSheetPropertiesController < ApplicationController
  before_filter :verify_group_authority
  # GET /answer_sheet_properties
  # GET /answer_sheet_properties.xml
  def index
    @group = Group.find(params[:group_id])
    @survey = @group.surveys.find(params[:survey_id])
    #TODO:
    @answer_sheet = AnswerSheet.find(params[:answer_sheet_id])
    @answer_sheet_properties = @answer_sheet.answer_sheet_properties
    respond_to do |format|
      format.html # index.html.erb
      format.xml  { render :xml => @answer_sheet_properties }
    end
  end

  # GET /answer_sheet_properties/1
  # GET /answer_sheet_properties/1.xml
  def show
    @group = Group.find(params[:group_id])
    @survey = @group.surveys.find(params[:survey_id])
    #TODO:
    @answer_sheet = AnswerSheet.find(params[:answer_sheet_id])
    @answer_sheet_property = @answer_sheet.answer_sheet_properties.find(params[:id])

    respond_to do |format|
      format.html # show.html.erb
      format.xml  { render :xml => @answer_sheet_property }
    end
  end

  def image
    @group = Group.find(params[:group_id])
    @survey = @group.surveys.find(params[:survey_id])
    #TODO:
    @answer_sheet = AnswerSheet.find(params[:answer_sheet_id])
    @answer_sheet_property = @answer_sheet.answer_sheet_properties.find(params[:id])

    send_file("#{MyAppConf::IMAGE_PATH_PREFIX}#{@answer_sheet_property.ocr_image}",
              :type => 'image/png',
              :disposition => 'inline')

#    response.headers['Content-Type'] = 'image/png'
#    image = ""
#    File.open("#{MyAppConf::IMAGE_PATH_PREFIX}#{@answer_sheet_property.ocr_image}").each do |buff|
#      image = image + buff
#    end
#    render :text => image, :layout => false
  end

  # GET /answer_sheet_properties/new
  # GET /answer_sheet_properties/new.xml
  def new
    @group = Group.find(params[:group_id])
    @survey = @group.surveys.find(params[:survey_id])
    #TODO:
    @answer_sheet = AnswerSheet.find(params[:answer_sheet_id])
    @answer_sheet_property = @answer_sheet.answer_sheet_properties.build

    respond_to do |format|
      format.html # new.html.erb
      format.xml  { render :xml => @answer_sheet_property }
    end
  end

  # GET /answer_sheet_properties/1/edit
  def edit
    @group = Group.find(params[:group_id])
    @survey = @group.surveys.find(params[:survey_id])
    #TODO:
    @answer_sheet = AnswerSheet.find(params[:answer_sheet_id])
    @answer_sheet_property = @answer_sheet.answer_sheet_properties.find(params[:id])
  end

  # POST /answer_sheet_properties
  # POST /answer_sheet_properties.xml
  def create
    @group = Group.find(params[:group_id])
    @survey = @group.surveys.find(params[:survey_id])
    #TODO:
    @answer_sheet = AnswerSheet.find(params[:answer_sheet_id])
    @answer_sheet_property = @answer_sheet.answer_sheet_properties.build(answer_sheet_property_params)
      if @answer_sheet_property.save
        redirect_to group_survey_answer_sheet_answer_sheet_properties_url(@group, @survey, @answer_sheet)
      else
        render :action => "new"
      end

    respond_to do |format|
      if @answer_sheet_property.save
        flash[:notice] = 'FAX調査項目を作成しました'
        format.html { redirect_to(@answer_sheet_property) }
        format.xml  { render :xml => @answer_sheet_property, :status => :created, :location => @answer_sheet_property }
      else
        format.html { render :action => "new" }
        format.xml  { render :xml => @answer_sheet_property.errors, :status => :unprocessable_entity }
      end
    end
  end

  # PUT /answer_sheet_properties/1
  # PUT /answer_sheet_properties/1.xml
  def update
    @group = Group.find(params[:group_id])
    @survey = @group.surveys.find(params[:survey_id])
    #TODO:
    @answer_sheet = AnswerSheet.find(params[:answer_sheet_id])
    @answer_sheet_property = AnswerSheetProperty.find(params[:id])
    if @answer_sheet_property.update_attributes(answer_sheet_property_params)
      redirect_to group_survey_answer_sheet_answer_sheet_properties_url(@group, @survey, @answer_sheet)
    else
      render :action => "edit"
    end
  end

  # DELETE /answer_sheet_properties/1
  # DELETE /answer_sheet_properties/1.xml
  def destroy
    @group = Group.find(params[:group_id])
    @survey = @group.surveys.find(params[:survey_id])
    @answer_sheet = AnswerSheet.find(params[:answer_sheet_id])
    @answer_sheet_property = AnswerSheetProperty.find(params[:id])
    @answer_sheet_property.destroy
    respond_to do |format|
      format.html { redirect_to(answer_sheet_properties_url) }
      format.xml  { head :ok }
    end
  end

  private

  def answer_sheet_property_params
    params.require(:answer_sheet_property).permit(:ocr_name, :ocr_value)
  end
end
