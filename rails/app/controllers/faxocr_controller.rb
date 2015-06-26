# -*- coding: utf-8 -*-
class FaxocrController < ApplicationController
  before_filter :verify_group_authority, :only => :direct_masquerade

  def index
     if (@current_group.is_administrator?)
       redirect_to :controller => 'groups', :action => 'index'
     else
       redirect_to group_url(@authorized_group ? @authorized_group.id : @current_group.id)
     end
  end

  def login
    if request.post?
      authn_user = User.authenticate(params[:login_name], params[:password])
      if authn_user
        session[:current_user] = authn_user
        return_uri = session[:return_uri]
        #session[:return_uri] = nil
        flash[:notice] = "ログインしました"
        redirect_to(return_uri || {:action => 'index'})
      else
        flash[:notice] = "ユーザ名、あるいは、パスワードが違います"
      end
    end

  end

  def group_select
    if request.post?
      role_mapping = RoleMapping.find_by_group_id_and_user_id(params[:gid], @current_user.id)
      if role_mapping
        session[:current_group] = Group.find_by_id(role_mapping.group_id)
        return_uri = session[:return_uri]
        session[:return_uri] = nil
        redirect_to(return_uri || {:action => 'index'})
        return
      else
        # The resuest did not have a group.
        flash.now[:notice] = "グループIDが不正です"
        return
      end
    end
  end

  def logout
    session[:current_group] = nil
    if session[:masq_from_user]
      session[:current_user] = session[:masq_from_user]
      session[:current_group] = session[:masq_from_group]
      session[:masq_from_user] = nil
      session[:masq_from_group] = nil
      flash[:notice] = "#{@current_user.full_name}がログアウトしました"
      redirect_to :action => 'index'
    else
      session[:current_user] = nil
      flash[:notice] = "ログアウトしました"
      redirect_to :action => 'login'
    end
  end

  def masquerade
    if request.post?
      masq_to_user = User.find_by_id(params[:id])
      unless masq_to_user
        flash[:notice] = "ユーザID #{params[:id]} は存在しません"
        redirect_to :action => 'index'
        return
      end
      if session[:masq_from_user]
        flash[:notice] = "既に代理ログインしています"
        redirect_to :action => 'index'
        return
      end
      authorized_group_ids = []
      @current_user.groups.each {|g| authorized_group_ids << g.id}
      unless @current_group.is_administrator? || has_role('m')
        flash[:notice] = "権限がありません"
        redirect_to :action => 'index'
        return
      end

      session[:masq_from_user] = @current_user
      session[:masq_from_group] = @current_group
      session[:current_user] = masq_to_user
      session[:current_group] = nil
      redirect_to :action => 'index'
      return
    end
    
    if @current_group.is_administrator?
      #@users = User.all
      @users = User.where("id != ?", @current_user.id)
    else
      #@users = @current_group.users
      @users = User.joins("INNER JOIN role_mappings ON users.id = role_mappings.user_id").where(["role_mappings.group_id = ? AND users.id != ?", @current_group.id, @current_user.id])
    end
  end

  def direct_masquerade
    # New method, to be invoked from group_user_index page.
    if @current_group.is_administrator? || has_role('m', :group_id => @authorized_group.id, :user_id => @current_user.id)
      masq_to_user = User.find_by_id(params[:id])
      unless masq_to_user
        flash[:notice] = "ユーザID #{params[:id]} は存在しません"
        redirect_to :action => 'index'
        return
      end
      if session[:masq_from_user]
        flash[:notice] = "既に代理ログインしています"
        redirect_to :action => 'index'
        return
      end

      session[:masq_from_user] = @current_user
      session[:masq_from_group] = @current_group
      session[:current_user] = masq_to_user
      session[:current_group] = @authorized_group
      flash[:notice] = "成功しました"
      redirect_to :action => 'index'
      return
    else
      flash[:notice] = "権限がありません"
      redirect_to :action => 'index'
      return
    end
  end

end
