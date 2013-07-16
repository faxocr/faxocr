class AddCellWidthToSheets < ActiveRecord::Migration
  def self.up
    add_column :sheets, :cell_width, :string
    add_column :sheets, :cell_height, :string
  end
  def self.down
    remove_column :sheets, :cell_width, :string
    remove_column :sheets, :cell_height, :string
  end
end
