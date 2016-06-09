#!/usr/bin/env ruby
require File.expand_path('../../config/environment',  __FILE__)
require "rubygems"
require "active_record"
require "yaml"
require "cgi"
config_db = "#{Rails.root}/config/database.yml"
db_env = :development
ActiveRecord::Base.configurations = YAML.load_file(config_db)
ActiveRecord::Base.establish_connection(db_env)
Dir.glob("#{Rails.root}/app/models/*.rb").each do |model|
  load model
end

accept_survey_statuses = []
# survey is opened
accept_survey_statuses << 1


accept_sheet_statuses = []
# sheet is opened
accept_sheet_statuses << 1
# sheet is stopping
#accept_sheet_statuses << 2
# sheet is finished
#accept_sheet_statuses << 3

print "<srMl>\n"
groups = Group.all
groups.each do |group|
  #print "  <!-- Group:#{group.group_name} -->\n"
  surveys = group.surveys.where(:status => accept_survey_statuses)
  if surveys != nil
    surveys.each do |survey|
      #print "  <!-- Survey:#{survey.survey_name} -->\n"
      srmlstr = survey.get_srml(accept_sheet_statuses)
      print srmlstr
    end
  end
end
print "</srMl>\n"
