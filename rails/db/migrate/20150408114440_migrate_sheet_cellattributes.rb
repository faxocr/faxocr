class MigrateSheetCellattributes < ActiveRecord::Migration
  def self.up
    create_table :sheet_cellattributes do |t|
      t.integer :sheet_id, :null => false

      t.timestamps
    end
    create_table :sheet_cellattribute_colwidths do |t|
      t.integer :sheet_cellattribute_id, :null => false
      t.integer :col_number,             :null => false
      t.float   :size,                   :null => false

      t.timestamps
    end
    create_table :sheet_cellattribute_rowheights do |t|
      t.integer :sheet_cellattribute_id, :null => false
      t.integer :row_number,             :null => false
      t.float   :size,                   :null => false

      t.timestamps
    end
    create_table :sheet_cellattribute_rowcolspans do |t|
      t.integer :sheet_cellattribute_id, :null => false
      t.integer :row_number,             :null => false
      t.integer :col_number,             :null => false
      t.integer :row_span,               :null => false
      t.integer :col_span,               :null => false

      t.timestamps
    end

    Sheet.all.each do |sheet|
      sheet_cellattribute = SheetCellattribute.new
      sheet_cellattribute.sheet_id = sheet.id
      sheet.sheet_cellattribute = sheet_cellattribute

      unless sheet.cell_width.blank?
        eval(sheet.cell_width).each do |i,v|
          sheet_cellattribute_colwidth = SheetCellattributeColwidth.new
          sheet_cellattribute_colwidth.sheet_cellattribute_id = sheet_cellattribute.id
          sheet_cellattribute_colwidth.col_number = i
          sheet_cellattribute_colwidth.size = v
          sheet.sheet_cellattribute.sheet_cellattribute_colwidths << sheet_cellattribute_colwidth
        end
      end
      unless sheet.cell_height.blank?
        eval(sheet.cell_height).each do |i,v|
          sheet_cellattribute_rowheight = SheetCellattributeRowheight.new
          sheet_cellattribute_rowheight.sheet_cellattribute_id = sheet_cellattribute.id
          sheet_cellattribute_rowheight.row_number = i
          sheet_cellattribute_rowheight.size = v
          sheet.sheet_cellattribute.sheet_cellattribute_rowheights << sheet_cellattribute_rowheight
        end
      end

      spans={}
      unless sheet.cell_colspan.blank?
        eval(sheet.cell_colspan).each do |rowcol, span|
          spans[rowcol] = {'col_span' => span, 'row_span' => 1}
        end
      end
      unless sheet.cell_rowspan.blank?
        eval(sheet.cell_rowspan).each do |rowcol, span|
          if spans.has_key?(rowcol)
            spans[rowcol].update({'row_span' => span})
          else
            spans[rowcol] = {'col_span' => 1, 'row_span' => span}
          end
        end
      end
      spans.each do |rowcol_num, rowcol_span|
        row_number, col_number = rowcol_num.split('_')
        sheet_cellattribute_rowcolspan = SheetCellattributeRowcolspan.new
        sheet_cellattribute_rowcolspan.sheet_cellattribute_id = sheet_cellattribute.id
        sheet_cellattribute_rowcolspan.row_number = row_number
        sheet_cellattribute_rowcolspan.col_number = col_number
        sheet_cellattribute_rowcolspan.col_span = rowcol_span['col_span']
        sheet_cellattribute_rowcolspan.row_span = rowcol_span['row_span']
        sheet.sheet_cellattribute.sheet_cellattribute_rowcolspans << sheet_cellattribute_rowcolspan
      end
      sheet.save!
    end

    remove_column :sheets, :cell_width
    remove_column :sheets, :cell_height
    remove_column :sheets, :cell_colspan
    remove_column :sheets, :cell_rowspan
  end

  def self.down
    add_column :sheets, :cell_width, :text
    add_column :sheets, :cell_height, :text
    add_column :sheets, :cell_colspan, :text
    add_column :sheets, :cell_rowspan, :text

    SheetCellattribute.all.each do |sc|
      colwidth = {}
      rowheight = {}
      colspan = {}
      rowspan = {}
      sc.sheet_cellattribute_colwidths.each do |cw|
        colwidth[cw.col_number] = cw.size
      end
      sc.sheet_cellattribute_rowheights.each do |rh|
        rowheight[rh.row_number] = rh.size
      end
      sc.sheet_cellattribute_rowcolspans.each do |rcs|
        row_col_str = "#{rcs.row_number}_#{rcs.col_number}"
        colspan[row_col_str] = rcs.col_span
        rowspan[row_col_str] = rcs.row_span
      end
      Sheet.find(sc.sheet_id).update_attributes!({
        :cell_width   => colwidth.inspect,
        :cell_height  => rowheight.inspect,
        :cell_colspan => colspan.inspect,
        :cell_rowspan => rowspan.inspect})
    end

    drop_table :sheet_cellattribute_rowcolspans
    drop_table :sheet_cellattribute_rowheights
    drop_table :sheet_cellattribute_colwidths
    drop_table :sheet_cellattributes
  end
end
# vim:set expandtab shiftwidth=2 tabstop=2 softtabstop=2:
