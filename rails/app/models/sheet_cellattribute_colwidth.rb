# -*- coding: utf-8 -*-
class SheetCellattributeColwidth < ActiveRecord::Base
  belongs_to :sheet_cellattribute

  def get_one_srml_entry
    generate_one_srml_entry(self)
  end

  def generate_one_srml_entry(entry)
    return "      <cellAttribute number=\"#{entry.col_number}\" length=\"#{entry.size}\"/>\n"
  end
end
