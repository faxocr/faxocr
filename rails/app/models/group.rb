class Group < ActiveRecord::Base
  has_many  :surveys, :dependent => :destroy
  has_many  :candidates, :dependent => :destroy
  has_many  :role_mappings, :dependent => :destroy
  has_many  :users, :through => :role_mappings

  validates_presence_of :group_name

  def is_administrator?
    if self.id == 1
      return true
    else
      return false
    end
  end
end
