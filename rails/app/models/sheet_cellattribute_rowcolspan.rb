# -*- coding: utf-8 -*-
class SheetCellattributeRowcolspan < ActiveRecord::Base
  belongs_to :sheet_cellattribute

  def get_one_srml_entry(row_or_col)
    generate_one_srml_entry(self, row_or_col)
  end

  def generate_one_srml_entry(entry, row_or_col)
    span = row_or_col=="row" ? entry.row_span : entry.col_span
    srmlstr = ""
    if span > 1
        srmlstr = "      <cellAttribute col=\"#{entry.col_number}\" row=\"#{entry.row_number}\" span=\"#{span}\"/>\n"
    end
    return srmlstr
  end
end
