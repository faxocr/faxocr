class CreateAnswerSheetProperties < ActiveRecord::Migration
  def self.up
    create_table :answer_sheet_properties do |t|
      t.integer :answer_sheet_id, :null => false
      t.string  :ocr_name,        :null => false
      t.string  :ocr_value
      t.string  :ocr_image

      t.timestamps
    end
    add_index :answer_sheet_properties, [:answer_sheet_id, :ocr_name], :unique => true
    add_index :answer_sheet_properties, :answer_sheet_id, :unique => false
  end

  def self.down
    drop_table :answer_sheet_properties
  end
end
