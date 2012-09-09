class CreateRoleMappings < ActiveRecord::Migration
  def self.up
    create_table :role_mappings do |t|
      t.integer :group_id,  :null => false
      t.integer :user_id,   :null => false
      t.string :role

      t.timestamps
    end
    add_index :role_mappings, [:group_id, :user_id], :unique => true
  end

  def self.down
    drop_table :role_mappings
  end
end
