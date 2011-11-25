class CreateSurveyUsers < ActiveRecord::Migration
  def self.up
    create_table :survey_users do |t|
      t.integer :survey_id,   :null => false
      t.integer :user_id,     :null => false
      t.boolean :owner,       :null => false

      t.timestamps
    end
    add_index :survey_users, [:survey_id, :user_id], :unique => true
  end

  def self.down
    drop_table :survey_users
  end
end
