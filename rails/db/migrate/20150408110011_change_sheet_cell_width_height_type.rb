class ChangeSheetCellWidthHeightType < ActiveRecord::Migration
  def self.up
    change_column :sheets, :cell_width, :text
    change_column :sheets, :cell_height, :text
    change_column :sheets, :cell_colspan, :text
    change_column :sheets, :cell_rowspan, :text
  end

  def self.down
    change_column :sheets, :cell_width, :string
    change_column :sheets, :cell_height, :string
    change_column :sheets, :cell_colspan, :string
    change_column :sheets, :cell_rowspan, :string
  end
end
# vim:set expandtab shiftwidth=2 tabstop=2 softtabstop=2:
