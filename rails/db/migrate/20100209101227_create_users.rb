class CreateUsers < ActiveRecord::Migration
  def self.up
    create_table :users do |t|
      t.string :login_name
      t.string :full_name
      t.string :hashed_password
      t.string :salt
      t.string :organization
      t.string :section
      t.string :tel_number
      t.string :fax_number
      t.string :email

      t.timestamps
    end
    add_index :users, :login_name, :unique => true
  end

  def self.down
    drop_table :users
  end
end
