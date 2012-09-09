class RoleMapping < ActiveRecord::Base
  belongs_to :group
  belongs_to :user
  validates_uniqueness_of :user_id, :scope => 'group_id'

  ROLES = [['ユーザ管理', 'u'], ['サーベイ管理', 's'], ['調査対象管理', 'c'], ['代理ログイン', 'm']]

  def printable_role
    role = self.role
    string = ""
    if /u/ =~ role
      string += "[ユーザ]"
    end
    if /s/ =~ role
      string += "[サーベイ]"
    end
    if /c/ =~ role
      string += "[調査対象]"
    end
    if /m/ =~ role
      string += "[代理]"
    end
    return string
  end

  def role_by_array
    role = self.role
    if role && role.length > 0
      return role.split(//)
    else
      return []
    end
  end

  def role_by_array=(roles_array)
    if roles_array
      self.role = roles_array.join
    end
  end
end
