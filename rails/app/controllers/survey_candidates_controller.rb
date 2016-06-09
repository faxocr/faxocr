class SurveyCandidatesController < ApplicationController
  # GET /survey_candidates
  # GET /survey_candidates.xml
  def index
    @group = Group.find(params[:group_id])
    @survey = @group.surveys.find(params[:survey_id])
    @survey_candidates = @survey.survey_candidates
    respond_to do |format|
      format.html # index.html.erb
      format.xml  { render :xml => @survey_candidates }
    end
  end

  # GET /survey_candidates/1
  # GET /survey_candidates/1.xml
  def show
    @group = Group.find(params[:group_id])
    @survey = @group.surveys.find(params[:survey_id])
    @survey_candidate = @survey.survey_candidates.find(params[:id])
    respond_to do |format|
      format.html # show.html.erb
      format.xml  { render :xml => @survey_candidate }
    end
  end

  # GET /survey_candidates/new
  # GET /survey_candidates/new.xml
  def new
    @group = Group.find(params[:group_id])
    @survey = @group.surveys.find(params[:survey_id])
    @survey_candidate = @survey.survey_candidates.build
    respond_to do |format|
      format.html # index.html.erb
      format.xml  { render :xml => @survey_candidate }
    end
  end

  # GET /survey_candidates/1/edit
  def edit
    @group = Group.find(params[:group_id])
    @survey = @group.surveys.find(params[:survey_id])
    @survey_candidate = @survey.survey_candidates.find(params[:id])
    respond_to do |format|
      format.html # index.html.erb
      format.xml  { render :xml => @survey_candidate }
    end
  end

  # POST /survey_candidates
  # POST /survey_candidates.xml
  def create
    @group = Group.find(params[:group_id])
    @survey = @group.surveys.find(params[:survey_id])
    @survey_candidate = @survey.survey_candidates.build(survey_candidate_params)
    if @survey_candidate.save
      redirect_to group_survey_survey_candidates_url(@group, @survey)
    else
      render :action => "new"
    end
  end

  # PUT /survey_candidates/1
  # PUT /survey_candidates/1.xml
  def update
    @group = Group.find(params[:group_id])
    @survey = Survey.find(params[:survey_id])
    @survey_candidate = SurveyCandidate.find(params[:id])
    @survey_candidate.role = ""
    if @survey_candidate.update_attributes(survey_candidate_params)
      redirect_to group_survey_survey_candidates_path(@group, @survey)
    else
      render :action => "edit"
    end
  end

  # DELETE /survey_candidates/1
  # DELETE /survey_candidates/1.xml
  def destroy
    @group = Group.find(params[:group_id])
    @survey = Survey.find(params[:survey_id])
    @survey_candidate = SurveyCandidate.find(params[:id])
    @survey_candidate.destroy
    respond_to do |format|
      format.html { redirect_to group_survey_survey_candidates_path(@group, @survey) }
      format.xml  { head :ok }
    end
  end

  private

  def survey_candidate_params
    params.require(:survey_candidate).permit(:survey_id, :candidate_id, :role_by_array => [])
  end
end
