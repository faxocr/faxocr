# -*- coding: utf-8 -*-
class Sheet < ActiveRecord::Base

  belongs_to :survey
  has_many :answer_sheets, :dependent => :destroy
  has_many :sheet_properties, :dependent => :destroy

  validates_presence_of :sheet_code, :sheet_name, :survey_id
  validates_uniqueness_of :sheet_code
  STATUS = [["クローズ", 0], ["オープン", 1], ["中断", 2], ["期間終了", 3]]
end
