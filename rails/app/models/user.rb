# -*- coding: utf-8 -*-
require "digest/sha1"
class User < ActiveRecord::Base
  has_many  :role_mappings, :dependent => :destroy
  has_many  :groups,  :through => :role_mappings

  validates_presence_of :login_name, :full_name
  validates_uniqueness_of :login_name
  validates_format_of :login_name, :with => /\A[0-9A-Za-z]/, :message =>"は半角英数字で入力してください。"
  attr_accessor :password_confirmation
  validates_confirmation_of :password

  validate :password_non_blank

  def self.authenticate(login_name, password)
    user = self.find_by_login_name(login_name)
    if user
      expected_password = encrypted_password(password, user.salt)
      if user.hashed_password != expected_password
        user = nil
      end
    end
    user
  end

  # "password" is a virtual attribute
  def password
    @password
  end

  def password=(pwd)
    @password = pwd
    return if pwd.blank?
    create_new_salt
    self.hashed_password = User.encrypted_password(self.password, self.salt)
  end

  def has_masquerade_role(group_id)
    @group_user = RoleMapping.find_by_user_id_and_group_id(self.id, group_id)
    if @group_user != nil
      role = @group_user.role
      if /m/ =~ role
        true
      else
        false
      end
    else
      if self.has_administrator_group
        true
      else
        false
      end
    end
  end

  def has_survey_role(group_id)
    @group_user = RoleMapping.find_by_user_id_and_group_id(self.id, group_id)
    if @group_user != nil
      role = @group_user.role
      if /s/ =~ role
        true
      else
        false
      end
    else
      if self.has_administrator_group
        true
      else
        false
      end
    end
  end

  def has_candidate_role(group_id)
    @group_user = RoleMapping.find_by_user_id_and_group_id(self.id, group_id)
    if @group_user != nil
      role = @group_user.role
      if /c/ =~ role
        true
      else
        false
      end
    else
      if self.has_administrator_group
        true
      else
        false
      end
    end
  end

  def has_user_role(group_id)
    @group_user = RoleMapping.find_by_user_id_and_group_id(self.id, group_id)
    if @group_user != nil
      role = @group_user.role
      if /u/ =~ role
        true
      else
        false
      end
    else
      if self.has_administrator_group
        true
      else
        false
      end
    end
  end

  def has_administrator_group
    @group_user = RoleMapping.find_by_user_id_and_group_id(self.id, 1)
    if @group_user != nil
        return true
    else
        return false
    end
  end

  private
  def password_non_blank
    errors.add(:password, "を入力して下さい") if hashed_password.blank?
  end

  def create_new_salt
    self.salt = self.object_id.to_s + rand.to_s
  end

  def self.encrypted_password(password, salt)
    string_to_hash = password + "faxocr" + salt
    Digest::SHA1.hexdigest(string_to_hash)
  end
end
