#!/usr/bin/ruby
# -*- coding: utf-8 -*-

require "rubygems"
require "active_record"
require "yaml"

rails_prefix = ARGV[0] || "./"
group = ARGV[1] || exit(0)
survey = ARGV[2] || exit(0) # XXX

config_db = rails_prefix + "/config/database.yml"
db_env = ""
cellinfo = Hash.new()

ActiveRecord::Base.configurations = YAML.load_file(config_db)
ActiveRecord::Base.establish_connection(db_env)
Dir.glob(rails_prefix + '/app/models/*.rb').each do |model|
  load model
end

#
# create a new survey
#
do
  @group = Group.find(group)
  @survey = @group.surveys.build(survey)
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
    print "success"
  else
    print "fail"
  end
end

#
# create survey properties
#

#
# [Property]
#
# survey_property.ocr_name
# survey_property.ocr_name_full
# survey_property.view_order
# survey_property.data_type
# sheet_property.position_x
# sheet_property.position_y
# sheet_property.colspan

props = []
props << ["テスト", "テスト", 1, "number", 1, 1, 1]
props << ["フォーム１", "フォーム１", 1, "number", 2, 2, 1]
props << ["テスト", "テスト", 1, "number", 3, 3, 1]
props << ["フォーム３", "フォーム３", 1, "number", 4, 4, 1]
props << ["フォーム２", "フォーム２", 1, "number", 5, 5, 1]

props.each do |prop|

  # object building
  @survey_property = @survey.survey_properties.build
  @survey_property.survey_id = survey		# integer
  @survey_property.ocr_name = prop[0]		# string
  @survey_property.ocr_name_full = prop[1]	# string
  @survey_property.view_order = prop[2]		# integer
  @survey_property.data_type = prop[3]		# string

  # save
  if @survey_property.save
    print  prop[0] + " success"
    cellinfo[@survey_property.id] = [prop[4], prop[5], prop[6]]
  else
    print  prop[0] + " fail"
  end
end

#
# create sheet/property mapping
#
do
  @group = Group.find(params[:group_id])
  @survey = @group.surveys.find(params[:survey_id])
  @sheet = @survey.sheets.build(params[:sheet])

  # sheet作成
  @sheet.sheet_code = "自動シート" + params[:survey_id].to_s
  @sheet.sheet_name = "自動シート" + params[:survey_id].to_s
  @sheet.survey_id = params[:survey_id]
  @sheet.block_width = 20 # XXX
  @sheet.block_height = 10 # XXX
  @sheet.status = 1

  # survey_property作成
  survey_properties = @survey.survey_properties
  survey_properties.each do |survey_property|

    prop = cellinfo[survey_property.id]

    sheet_property = SheetProperty.new
    sheet_property.survey_property_id = survey_property.id # survey_propertyからコピー
    sheet_property.cellinfo_x = prop[0]
    sheet_property.cellinfo_y = prop[1]
    sheet_property.colspan = prop[2]
    @sheet.sheet_properties << sheet_property
  end

  # save
  if @sheet.save
    print  "survey_property: save success"
  else
    print  "survey_property: save fail"
  end
end

exit(0)
