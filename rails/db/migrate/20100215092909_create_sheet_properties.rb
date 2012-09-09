class CreateSheetProperties < ActiveRecord::Migration
  def self.up
    create_table :sheet_properties do |t|
      t.integer :position_x
      t.integer :position_y
      t.integer :colspan
      t.integer :sheet_id
      t.integer :survey_property_id

      t.timestamps
    end
  end

  def self.down
    drop_table :sheet_properties
  end
end
