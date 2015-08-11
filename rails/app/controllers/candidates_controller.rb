class CandidatesController < ApplicationController
  before_filter :verify_group_authority
  before_filter :verify_candidate_authority, :only => [:show, :edit, :update, :destroy]
  def verify_candidate_authority
    super(:candidate_id => params[:id])
  end
  # GET /candidates
  # GET /candidates.xml
  def index
    @group = @authorized_group
    @candidates = @group.candidates
    respond_to do |format|
      format.html # index.html.erb
      format.xml  { render :xml => @candidates }
    end
  end
  # GET /candidates/1
  # GET /candidates/1.xml
  def show
    @group = @authorized_group
    @candidate = @authorized_candidate
    respond_to do |format|
      format.html # show.html.erb
      format.xml  { render :xml => @candidate }
    end
  end

  # GET /candidates/new
  # GET /candidates/new.xml
  def new
    @group = @authorized_group
    @candidate = @group.candidates.build    
    respond_to do |format|
      format.html # new.html.erb
      format.xml  { render :xml => @candidate }
    end
  end

  # GET /candidates/1/edit
  def edit
    @group = Group.find(params[:group_id])
    @candidate = @group.candidates.find(params[:id])
    respond_to do |format|
      format.html # new.html.erb
    end
  end

  # POST /candidates
  # POST /candidates.xml
  def create
    @group = Group.find(params[:group_id])
    @candidate = @group.candidates.build(candidate_params)
    if @candidate.save
      redirect_to group_candidates_url(@group)
    else
      render :action => "new"
    end
  end

  # PUT /candidates/1
  # PUT /candidates/1.xml
  def update
    @group = Group.find(params[:group_id])
    @candidate = Candidate.find(params[:id])
    if @candidate.update_attributes(candidate_params)
      redirect_to group_candidate_url(@group, @candidate)
    else
      render :action => "edit"
    end
  end

  # DELETE /candidates/1
  # DELETE /candidates/1.xml
  def destroy
    @group = Group.find(params[:group_id])
    @candidate = Candidate.find(params[:id])
    @candidate.destroy

    respond_to do |format|
      format.html { redirect_to group_candidates_path(@group) }
      format.xml  { head :ok }
    end
  end

  private

  def candidate_params
    params.require(:candidate).permit(:group_id, :candidate_name, :candidate_code, :tel_number, :fax_number)
  end
end
