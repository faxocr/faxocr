# -*- coding: utf-8 -*-

class SurveyCandidate < ApplicationRecord
  belongs_to :survey
  belongs_to :candidate
  ROLES = [['報告受付対象', 'r'], ['集計レポート送信', 's']]

  validates_uniqueness_of :candidate_id, :scope => "survey_id"

  def printable_role
    role = self.role
    string = ""
    if /r/ =~ role
      string += "[受付]"
    end
    if /s/ =~ role
      string += "[送信]"
    end
    return string
  end
  def has_sendreport_role
    role = self.role
    if /s/ =~ role
      return true
    else
      return false
    end
  end
  def has_receivereport_role
    role = self.role
    if /r/ =~ role
      return true
    else
      return false
    end
  end

  def role_by_array
    role = self.role
    if role && role.length > 0
      return role.split(//)
    else
      return []
    end
  end

  def role_by_array=(roles_array)
    if roles_array
      self.role = roles_array.join
    end
  end
end
