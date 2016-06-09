# -*- coding: utf-8 -*-
# Filters added to this controller apply to all controllers in the application.
# Likewise, all the methods added will be available for all controllers.

class ApplicationController < ActionController::Base
  before_filter :authorize, :except => :login
  helper :all # include all helpers, all the time
  protect_from_forgery # See ActionController::RequestForgeryProtection for details

  # Scrub sensitive parameters from your log
  # filter_parameter_logging :password

protected

  def authorize
    @current_user = User.find_by_id(session[:current_user])
    @current_group = Group.find_by_id(session[:current_group])
    @masq_from_user = User.find_by_id(session[:masq_from_user])
    @masq_from_group = Group.find_by_id(session[:masq_from_group])
    # Checks a session that it has a user.
    unless @current_user
      # First, this code runs a session that it is start.
      # It is going to set a user to a session in the 
      # login of faxocr.
      session[:return_uri] = request.url
      flash[:notice] = "ログインして下さい"
      redirect_to :controller => 'faxocr', :action => 'login'
      return
    end

    # Checks a session that it has a group.
    unless @current_group
      @authorized_groups = @current_user.groups
      # Skip further verification to show group selection page
      # or receive posted group selection.
      if session[:wait_group_selection] == true || params[:gid]
        session[:wait_group_selection] = nil
        return
      end
      if @authorized_groups.length == 1
        # In after login, the login user goes to request url if they has one group.
        @current_group = session[:current_group] = @authorized_groups[0]
        session[:return_uri] = nil
        return
      elsif @authorized_groups.length > 1
        session[:wait_group_selection] = true
        #session[:return_uri] = request.url
        flash[:notice] = "ユーザ #{@current_user.full_name} は、複数のグループに属しています。一つを選択して下さい。"
        redirect_to :controller => 'faxocr', :action => 'group_select'
        return
      else
        # TODO: Error, user does not belong to any group.
      end
    else
        # TODO:
    end
  end

  def verify_group_authority(args = {})
    group_id = args[:group_id] || params[:group_id]
    if group_id
      group_id = group_id.to_i
      if @current_group.id == group_id
        @authorized_group = @current_group
      elsif @current_group.is_administrator?
        @authorized_group = Group.find(group_id)
      end
      unless @authorized_group
        access_violation
      end
    end
  end

  def verify_survey_authority(args = {})
    survey_id = args[:survey_id] || params[:survey_id]
    survey_code = args[:survey_code] || params[:survey_code]
    if survey_id
      survey = Survey.find_by_id(survey_id.to_i)
    elsif survey_code
      survey = Survey.find_by_survey_code(survey_code)
    else
      return
    end
    if survey
      verify_group_authority(:group_id => survey.group_id)
      @authorized_survey = survey
    else
      access_violation # Given survey does not exist
    end
  end

  def verify_candidate_authority(args = {})
    candidate_id = args[:candidate_id] || params[:candidate_id]
    candidate_code = args[:candidate_code] || params[:candidate_code]
    if candidate_id
      candidate = Candidate.find_by_id(candidate_id.to_i)
    elsif candidate_code
      candidate = Candidate.find_by_candidate_code(candidate_code)
    else
      return
    end
    if candidate
      verify_group_authority(:group_id => candidate.group_id)
      @authorized_candidate = candidate
    else
      access_violation # Given candidate does not exist
    end
  end

  def verify_sheet_authority(args = {})
    sheet_id = args[:sheet_id] || params[:sheet_id]
    sheet_code = args[:sheet_code] || params[:sheet_code]
    if sheet_id
      sheet = Sheet.find_by_id(sheet_id.to_i)
    elsif sheet_code
      sheet = Sheet.find_by_sheet_code(sheet_code)
    else
      return
    end
    if sheet
      verify_survey_authority(:survey_id => sheet.survey_id)
      @authorized_sheet = sheet
    else
      access_violation # Given sheet does not exist
    end
  end

  def verify_answer_sheet_authority(args = {})
    answer_sheet_id = args[:answer_sheet_id] || params[:answer_sheet_id]
    if answer_sheet_id
      answer_sheet = AnswerSheet.find_by_id(answer_sheet_id.to_i)
    else
      return
    end
    if answer_sheet
      verify_sheet_authority(:sheet_id => answer_sheet.sheet_id)
      @authorized_answer_sheet = answer_sheet
    else
      access_violation # Given answer_sheet does not exist
    end
  end

  # TODO
  # verify_survey_property_authority
  # verify_

  def verify_role(role_char)
    if @current_group.is_administrator? || has_role(role_char)
      true
    else
      access_violation
    end
  end

  def has_role(role_char, args = {})
    group_id = args[:group_id] || @current_group.id
    user_id = args[:user_id] || @current_user.id
    role_like = "%#{role_char}%"
    if RoleMapping.where(['group_id = ? and user_id = ? and role like ?', group_id, user_id, role_like]).length > 0
      true
    else
      false
    end
  end

  def access_violation
    # This code is not fully correct, need to be fixed
    send_data("Permission denied.", :status => "403 Forbidden")
  end

  def debug_mode
    value = `. #{Rails.root}/../etc/faxocr.conf; echo $DEBUG_MODE`
    value.chomp
  end

  def page_size
    value = `. #{Rails.root}/../etc/faxocr.conf; echo $PAGE_SIZE`
    value.chomp
    if value.to_i == 0
      value = 10
    end
    value
  end

end
