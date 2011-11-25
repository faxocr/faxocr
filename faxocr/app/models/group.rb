class Group < ActiveRecord::Base
  has_many  :surveys
  has_many  :candidates
  has_many  :role_mappings
  has_many  :users, :through => :role_mappings

  def is_administrator?
    if self.id == 1
      return true
    else
      return false
    end
  end
end
