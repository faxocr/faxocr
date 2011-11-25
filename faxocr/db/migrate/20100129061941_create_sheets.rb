class CreateSheets < ActiveRecord::Migration
  def self.up
    create_table :sheets do |t|
      t.string  :sheet_code,      :null => false
      t.string  :sheet_name,      :null => false
      t.integer :survey_id,       :null => false
      t.integer :block_width,     :null => false
      t.integer :block_height,    :null => false
      t.integer :status,          :null => false
#      t.text    :srml,            :null => false  #TODO: Scaffold

      t.timestamps
    end
    add_index :sheets, :sheet_code, :unique => true
    add_index :sheets, :survey_id
  end

  def self.down
    drop_table :sheets
  end
end
