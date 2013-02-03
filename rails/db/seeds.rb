# -*- coding: utf-8 -*-
# This file should contain all the record creation needed to seed the database with its default values.
# The data can then be loaded with the rake db:seed (or created alongside the db with db:setup).
#
# Examples:
#   
#   cities = City.create([{ :name => 'Chicago' }, { :name => 'Copenhagen' }])
#   Major.create(:name => 'Daley', :city => cities.first)

#
# Admin user and group
#

if User.count == 0
  admin_user = User.new # id = 1
  admin_user.login_name = 'admin'
  admin_user.full_name = '管理者'
  admin_user.password = 'admin'

  admin_group = Group.new # id = 1
  admin_group.group_name = '管理者グループ'

  admin_user.groups << admin_group
  admin_user.save

  gu = RoleMapping.find_by_group_id_and_user_id(admin_user.id, admin_group.id)
  gu.role = 'umsc'
  gu.save
end

#
# Fallback surveys, sheets and candidates
#

if Survey.count == 0
  fb_survey = Survey.new # id = 1
  fb_survey.survey_name = '管理者システム用サーベイ'
  fb_survey.group_id = admin_group.id
  fb_survey.status = '0'
  fb_survey.save

  fb_candidate = Candidate.new # id = 1
  fb_candidate.candidate_code = '99999'
  fb_candidate.candidate_name = '不明な調査対象'
  fb_candidate.group_id = admin_group.id
  fb_candidate.tel_number = 'FALLBACK'
  fb_candidate.fax_number = 'FALLBACK'
  fb_candidate.save

  fb_survey.candidates << fb_candidate
  fb_survey.save

  fb_sheet = Sheet.new # id = 1
  fb_sheet.sheet_code = 'FALLBACK'
  fb_sheet.sheet_name = '不明なシート'
  fb_sheet.survey_id = fb_survey.id
  fb_sheet.status = '0'
  fb_sheet.block_width = '100'
  fb_sheet.block_height = '100'
  fb_sheet.save
end
