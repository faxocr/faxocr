class CreateAnswerSheets < ActiveRecord::Migration
  def self.up
    create_table :answer_sheets do |t|
      t.timestamp :date
      t.string    :sender_number
      t.string    :receiver_number
      t.integer   :sheet_id
      t.integer   :candidate_id
      t.string    :analyzed_sheet_code
      t.string    :analyzed_candidate_code
      t.string    :sheet_image
      t.boolean   :need_check
      t.integer   :srerror

      t.timestamps
    end
    add_index :answer_sheets, :sheet_id, :unique => false
    add_index :answer_sheets, :candidate_id, :unique => false
  end

  def self.down
    drop_table :answer_sheets
  end
end
