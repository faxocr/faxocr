class AddMergedCellSpanInfoToSheets < ActiveRecord::Migration
  def self.up
    add_column :sheets, :cell_colspan, :string
    add_column :sheets, :cell_rowspan, :string
  end
  def self.down
    remove_column :sheets, :cell_colspan
    remove_column :sheets, :cell_rowspan
  end
end
