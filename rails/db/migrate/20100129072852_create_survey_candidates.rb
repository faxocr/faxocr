class CreateSurveyCandidates < ActiveRecord::Migration
  def self.up
    create_table :survey_candidates do |t|
      t.integer :survey_id,    :null => false
      t.integer :candidate_id, :null => false
      t.string  :role

      t.timestamps
    end
    add_index :survey_candidates, [:survey_id, :candidate_id], :unique => true
  end

  def self.down
    drop_table :survey_candidates
  end
end
