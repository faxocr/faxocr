# -*- coding: utf-8 -*-
class UsersController < ApplicationController
  before_filter :verify_group_authority
  before_filter :verify_user_id, :except => [:index, :new, :create]
  before_filter :verify_role, :except => [:index, :show, :edit, :edit_self, :update_self]
  
  # GET /users
  # GET /users.xml
  def index
    if @current_group.is_administrator? || has_role('u')
      @masqueradable = true;
    else
      redirect_to :action => :edit_self, :id => @current_user.id
      return
    end
    @group = @authorized_group || @current_group
    #@group = Group.find(params[:group_id])
    @users = User.select("u.*").
        joins("AS u INNER JOIN role_mappings AS gu ON u.id = gu.user_id").
        where("gu.group_id = ?", @group.id).
        order("u.login_name")

    respond_to do |format|
      format.html # index.html.erb
      format.xml  { render :xml => @users }
    end
  end

  # GET /users/1
  # GET /users/1.xml
  def show
    @group = @authorized_group
    @user = User.find(params[:id])

    respond_to do |format|
      format.html # show.html.erb
      format.xml  { render :xml => @user }
    end
  end

  # GET /users/new
  # GET /users/new.xml
  def new
    @group = @authorized_group
    @user = User.new
    @role_mapping = RoleMapping.new

    respond_to do |format|
      format.html # new.html.erb
      format.xml  { render :xml => @user }
    end
  end

  # GET /users/1/edit
  def edit
    @group = @authorized_group
    @user = User.find(params[:id])
    @role_mapping = RoleMapping.find_by_group_id_and_user_id(@group.id, @user.id)
  end

  # GET /users/1/edit_self
  def edit_self # Currently supports pass-change only
    @group = @authorized_group
    @user = User.find(params[:id])
  end

  def update_self # Currently supports pass-change only
    @group = @authorized_group
    @user = User.find(params[:id])

    user_attr = Hash.new
    user_attr['password'] = params[:user]['password']
    user_attr['password_confirmation'] = params[:user]['password_confirmation']

    respond_to do |format|
      if @user.update_attributes(user_attr)
        flash[:notice] = "ユーザ #{@user.login_name} の情報を更新しました"
        format.html { redirect_to group_users_url(@group) }
        format.xml  { head :ok }
      else
        format.html { render :action => "edit_self" }
        format.xml  { render :xml => @user.errors, :status => :unprocessable_entity }
      end
    end
  end

  # POST /users
  # POST /users.xml
  def create
    @group = @authorized_group
    @user = User.new(params[:user])
    @role_mapping = RoleMapping.new
    @role_mapping.group = @group
    @role_mapping.user = @user
    @role_mapping.role = ""
    @role_mapping.role_by_array = params[:role_by_array]

    respond_to do |format|
      if @user.save && @role_mapping.save
        flash[:notice] = "ユーザ #{@user.login_name} を作成しました"
        format.html { redirect_to group_users_url(@group) }
        format.xml  { render :xml => @user, :status => :created, :location => @user }
      else
        format.html { render :action => "new" }
        format.xml  { render :xml => @user.errors, :status => :unprocessable_entity }
      end
    end
  end

  # PUT /users/1
  # PUT /users/1.xml
  def update
    @group = @authorized_group
    @user = User.find(params[:id])
    if @user.id != @current_user.id
      role_mapping = RoleMapping.find_by_group_id_and_user_id(@group.id, @user.id)
      role_mapping_attr = Hash.new
      role_mapping_attr['group_id'] = @group.id
      role_mapping_attr['user_id'] = @user.id
      role_mapping_attr['role_by_array'] = params[:role_by_array]
    end

    respond_to do |format|
      if role_mapping
        role_mapping.role = ""
        result = @user.update_attributes(params[:user]) && role_mapping.update_attributes(role_mapping_attr)
      else
        result = @user.update_attributes(params[:user])
      end
      if result
        flash[:notice] = "ユーザ #{@user.login_name} の情報を更新しました"
        format.html { redirect_to group_users_url(@group) }
        format.xml  { head :ok }
      else
        format.html { render :action => "edit" }
        format.xml  { render :xml => @user.errors, :status => :unprocessable_entity }
      end
    end
  end

  # DELETE /users/1
  # DELETE /users/1.xml
  def destroy
    @group = @authorized_group
    @user = User.find(params[:id])
    @user.destroy

    respond_to do |format|
      format.html { redirect_to group_users_path(@group) }
      format.xml  { head :ok }
    end
  end

private

  def verify_user_id
    user_id = params[:id].to_i
    if user_id
      if @current_group.is_administrator? || user_id == @current_user.id
        return true
      elsif has_role('u')
        @current_group.users.each do |user|
          return true if user_id == user.id
        end
      end
      access_violation
    end
  end

  def verify_role
    super('u')
  end

end
