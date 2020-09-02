# -*- coding: utf-8 -*-
class SheetCellattribute < ApplicationRecord
  belongs_to :sheet
  has_many :sheet_cellattribute_colwidths,   :dependent => :destroy
  has_many :sheet_cellattribute_rowheights,  :dependent => :destroy
  has_many :sheet_cellattribute_rowcolspans, :dependent => :destroy

  def get_one_srml_entry
    generate_one_srml_entry(self)
  end

  def generate_one_srml_entry(sheet_cellattribute)
    srmlstr = ""
    srmlstr
  end
end
