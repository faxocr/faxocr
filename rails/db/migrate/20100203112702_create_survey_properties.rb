class CreateSurveyProperties < ActiveRecord::Migration
  def self.up
    create_table :survey_properties do |t|
      t.integer :survey_id,       :null => false
      t.string  :ocr_name,        :null => false
      t.string  :ocr_name_full,   :null => false
      t.integer :view_order,      :null => false
      t.string  :data_type,       :null => false

      t.timestamps
    end
    add_index :survey_properties, [:survey_id, :ocr_name], :unique => true
  end

  def self.down
    drop_table :survey_properties
  end
end
