#!/usr/bin/ruby
# -*- coding: utf-8 -*-

require "rubygems"
require "active_record"
require "yaml"

rails_prefix = ARGV[0] || "./"
group = ARGV[1] || exit(0)
survey = ARGV[2] || exit(0) # XXX: is this needed?

config_db = rails_prefix + "/config/database.yml"
# XXX
#db_env = "" 
db_env = "development"
cellinfo = Hash.new()

ActiveRecord::Base.configurations = YAML.load_file(config_db)
ActiveRecord::Base.establish_connection(db_env)

Dir.glob(rails_prefix + '/app/models/*.rb').each do |model|
  load model
end

# Initialization
@group = Group.find(group)

#
# create a default candidate (hardcoded)
#
@candidate = @group.candidates.build
@candidate.candidate_code = "00000" 		# (string, not null)
@candidate.candidate_name = "一般報告者"	# (string, not null)
@candidate.group_id = group			# (int, not null)
@candidate.tel_number = "03-1111-1111"		# (string)
@candidate.fax_number = "03-1111-1111"		# (string)
if @candidate.save
  print "default candidate: success\n"
else
  print "default candidate: fail\n"
end

#
# create a new survey
#
@survey = @group.surveys.build
@survey.survey_name = "自動生成サーベイ" # XXX
@survey.status = 0 # XXX: NEEDS INITIAL VALUE HERE
@survey.report_header = ""
@survey.report_footer = ""

@candidates = @group.candidates
@candidates.each do |candidate|
  survey_candidate = SurveyCandidate.new
  survey_candidate.candidate_id = candidate.id
  survey_candidate.role = 'sr'
  @survey.survey_candidates << survey_candidate
end
if @survey.save
  print "survey candidate: success\n"
else
  print "survey candidate: fail\n"
  exit(0)
end

#
# create survey properties
#

#
# [Property]
#
# [0] survey_property.ocr_name
# [1] survey_property.ocr_name_full
# [2] survey_property.view_order
# [3] survey_property.data_type
# [4] sheet_property.position_x
# [5] sheet_property.position_y
# [6] sheet_property.colspan

props = []
props << ["テスト", "テスト", 1, "number", 1, 11, 1]
props << ["フォーム１", "フォーム１", 1, "number", 2, 12, 1]
props << ["テスト2",     "テストル",   1, "number", 3, 13, 2]
props << ["フォーム３", "フォーム３", 1, "number", 4, 14, 1]
props << ["フォーム２", "フォーム２", 1, "number", 5, 15, 1]

props.each do |prop|

  # object building
  @survey_property = @survey.survey_properties.build
  @survey_property.survey_id = survey		# integer
  @survey_property.ocr_name = prop[0]		# string (must be unique!)
  @survey_property.ocr_name_full = prop[1]	# string
  @survey_property.view_order = prop[2]		# integer
  @survey_property.data_type = prop[3]		# string
  # print prop[0] + "/" + prop[1] + "\n"

  # save
  if @survey_property.save
    print  "survey property " + prop[0] + ": success\n"
    cellinfo[@survey_property.id] = [prop[4], prop[5], prop[6]]
  else
    print  "survey property " + prop[0] + ": fail\n"
    exit(0)
  end
end

#
# create sheet/property mapping
#
@sheet = @survey.sheets.build

# sheet作成
@sheet.sheet_code = "自動生成シート" + survey.to_s
@sheet.sheet_name = "自動生成シート" + survey.to_s
@sheet.survey_id = survey
@sheet.block_width = 20 # XXX
@sheet.block_height = 10 # XXX
@sheet.status = 1
# save
if @sheet.save
  print  "sheet: save success\n"
else
  print  "sheet: save fail\n"
  exit(0)
end

#
# sheet_property generation
#
survey_properties = @survey.survey_properties
survey_properties.each do |@survey_property|

  prop = cellinfo[@survey_property.id]
  if prop.nil? then
    next
  end
  # survey_propertyからコピー
  sheet_property = SheetProperty.new
  sheet_property.sheet_id = @sheet.object_id
  sheet_property.survey_property_id = @survey_property.id
  sheet_property.position_x = prop[0]
  sheet_property.position_y = prop[1]
  sheet_property.colspan = prop[2]
  @sheet.sheet_properties << sheet_property

  # print "> " + prop[0].to_s + "/" + prop[1].to_s + "/" + prop[2].to_s + "\n"
end

# save
if @sheet.save
  print  "sheet_property: save success\n"
else
  print  "sheet_property: save fail\n"
  exit(0)
end

exit(0)
