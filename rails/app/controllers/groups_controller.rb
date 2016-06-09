# -*- coding: utf-8 -*-
class GroupsController < ApplicationController
  before_filter :verify_group_authority
  def verify_group_authority
    super(:group_id => params[:id])
  end
  # GET /groups
  # GET /groups.xml
  def index
    if @current_user.has_administrator_group
      @groups = Group.all
    else
      @groups = []
      @groups << @current_group
    end
    respond_to do |format|
      format.html # index.html.erb
      format.xml  { render :xml => @groups }
    end
  end

  # GET /groups/1
  # GET /groups/1.xml
  def show
    @group = Group.find(params[:id])
    @surveys = @group.surveys
    # @candidates = @group.candidates
    # @sht_id = AnswerSheet.last.nil? ? 1 : AnswerSheet.last.id + 1
    @sht_id = Sheet.last.nil? ? 1 : Sheet.last.id + 1

    respond_to do |format|
      format.html # show.html.erb
      format.xml  { render :xml => @group }
    end
  end

  # GET /groups/new
  # GET /groups/new.xml
  def new
    @group = Group.new

    respond_to do |format|
      format.html # new.html.erb
      format.xml  { render :xml => @group }
    end
  end

  # GET /groups/1/edit
  def edit
    @group = Group.find(params[:id])
  end

  # POST /groups
  # POST /groups.xml
  def create
    @group = Group.new(group_params)

    respond_to do |format|
      if @group.save
        flash[:notice] = 'グループを作成しました'
        format.html { redirect_to(@group) }
        format.xml  { render :xml => @group, :status => :created, :location => @group }
      else
        format.html { render :action => "new" }
        format.xml  { render :xml => @group.errors, :status => :unprocessable_entity }
      end
    end
  end

  # PUT /groups/1
  # PUT /groups/1.xml
  def update
    @group = Group.find(params[:id])

    respond_to do |format|
      if @group.update_attributes(group_params)
        flash[:notice] = 'グループを更新しました'
        format.html { redirect_to(@group) }
        format.xml  { head :ok }
      else
        format.html { render :action => "edit" }
        format.xml  { render :xml => @group.errors, :status => :unprocessable_entity }
      end
    end
  end

  # DELETE /groups/1
  # DELETE /groups/1.xml
  def destroy
    @group = Group.find(params[:id])
    @group.destroy

    respond_to do |format|
      format.html { redirect_to(groups_url) }
      format.xml  { head :ok }
    end
  end

  def report
    @group = Group.find(params[:id])
    @surveys = @group.surveys
    datetime = DateTime.now

    @repyears = []
    i = 2
    while i >= 0 do
      tmpdatetime = datetime - (365 * i)
      repyear = [tmpdatetime.strftime("%Y"), tmpdatetime.strftime("%Y")]
      @repyears << repyear
      i -= 1
    end

    @repmonths = []
    datetime = DateTime.now
    i = 5
    while i >= 0 do
      tmpdatetime = datetime - (31 * i)
      repmonth = [tmpdatetime.strftime("%m"), tmpdatetime.strftime("%Y"), tmpdatetime.strftime("%m")]
      i -= 1
      @repmonths << repmonth
    end

    @repdays = []
    datetime = DateTime.now
    i = 6
    while i >= 0 do
      tmpdatetime = datetime - i
      repday = [tmpdatetime.strftime("%d"), tmpdatetime.strftime("%Y"), tmpdatetime.strftime("%m"), tmpdatetime.strftime("%d")]
      i -= 1
      @repdays << repday
    end

    respond_to do |format|
      format.html # show.html.erb
      format.xml  { render :xml => @group }
    end
  end

  private

  def group_params
    params.require(:group).permit(:group_name)
  end
end
