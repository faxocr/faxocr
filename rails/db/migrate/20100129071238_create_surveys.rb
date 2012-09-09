class CreateSurveys < ActiveRecord::Migration
  def self.up
    create_table :surveys do |t|
      t.string  :survey_name,       :null => false
      t.integer :group_id,          :null => false
      t.integer :status,            :null => false
      t.integer :sheet_id
      t.text    :report_header
      t.text    :report_footer

      t.timestamps
    end
    add_index :surveys, :group_id
  end

  def self.down
    drop_table :surveys
  end
end
