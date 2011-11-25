class CreateCandidates < ActiveRecord::Migration
  def self.up
    create_table :candidates do |t|
      t.string  :candidate_code,      :null => false
      t.string  :candidate_name,      :null => false
      t.integer :group_id,            :null => false
      t.string  :tel_number
      t.string  :fax_number

      t.timestamps
    end
    add_index :candidates, :candidate_code, :unique => true
    add_index :candidates, :group_id
  end

  def self.down
    drop_table :candidates
  end
end
