# -*- coding: utf-8 -*-
class RoleMappingsController < ApplicationController
  before_filter :administrator_only
  # GET /role_mappings
  # GET /role_mappings.xml
  def index
    @role_mappings = RoleMapping.where("group_id, user_id")

    respond_to do |format|
      format.html # index.html.erb
      format.xml  { render :xml => @role_mappings }
    end
  end

  # GET /role_mappings/1
  # GET /role_mappings/1.xml
  def show
    @role_mapping = RoleMapping.find(params[:id])

    respond_to do |format|
      format.html # show.html.erb
      format.xml  { render :xml => @role_mapping }
    end
  end

  # GET /role_mappings/new
  # GET /role_mappings/new.xml
  def new
    @role_mapping = RoleMapping.new

    respond_to do |format|
      format.html # new.html.erb
      format.xml  { render :xml => @role_mapping }
    end
  end

  # GET /role_mappings/1/edit
  def edit
    @role_mapping = RoleMapping.find(params[:id])
  end

  # POST /role_mappings
  # POST /role_mappings.xml
  def create
    @role_mapping = RoleMapping.new(params[:role_mapping])
    @role_mapping.role = ""
    respond_to do |format|
      if @role_mapping.save
        flash[:notice] = '役割の設定を行いました'
        format.html { redirect_to(@role_mapping) }
        format.xml  { render :xml => @role_mapping, :status => :created, :location => @role_mapping }
      else
        format.html { render :action => "new" }
        format.xml  { render :xml => @role_mapping.errors, :status => :unprocessable_entity }
      end
    end
  end

  # PUT /role_mappings/1
  # PUT /role_mappings/1.xml
  def update
    @role_mapping = RoleMapping.find(params[:id])
    @role_mapping.role = ""

    respond_to do |format|
      if @role_mapping.update_attributes(params[:role_mapping])
        flash[:notice] = '役割の更新を行いました'
        format.html { redirect_to(@role_mapping) }
        format.xml  { head :ok }
      else
        format.html { render :action => "edit" }
        format.xml  { render :xml => @role_mapping.errors, :status => :unprocessable_entity }
      end
    end
  end

  # DELETE /role_mappings/1
  # DELETE /role_mappings/1.xml
  def destroy
    @role_mapping = RoleMapping.find(params[:id])
    @role_mapping.destroy

    respond_to do |format|
      format.html { redirect_to(role_mappings_url) }
      format.xml  { head :ok }
    end
  end

private

  def administrator_only
    unless @current_group.is_administrator?
      access_violation
    end
  end
end
