#!/usr/bin/env ruby
# -*- coding: utf-8 -*-
require File.expand_path('../../config/environment',  __FILE__)
rails_prefix = Rails.root
require "rubygems"
require "active_record"
require "yaml"
require "cgi"
require "erb"

class ReportHtml
  def initialize
    @survey = nil
    @answer_sheets = nil
    set_datetime("20100101020304")
    @prefix_image = "."
    #@all_image = true
  end
  def set_datetime(datetime)
    @datetime = DateTime.strptime(str=datetime, fmt='%Y%m%d%H%M%S')
    @datetime_begin = @datetime.strftime("%Y/%m/%d 00:00:00")
    @datetime_end = @datetime.strftime("%Y/%m/%d 23:59:59")
    @place_holder = Hash.new
    @place_holder.store('YEAR', @datetime.strftime("%Y"))
    @place_holder.store('MONTH', @datetime.strftime("%m"))
    @place_holder.store('DAY', @datetime.strftime("%d"))
  end
  def datetime
    @datetime
  end
  def set_survey_id(survey_id)
    @survey = Survey.find(survey_id)
    @survey_candidates = @survey.survey_candidates
    sheet_ids = @survey.sheet_ids

    @answer_sheets = []
    @survey_candidates.each do |survey_candidate|
      if survey_candidate.has_receivereport_role
        @answer_sheet = AnswerSheet.find_by_sheet_id_and_candidate_id(sheet_ids, survey_candidate.candidate_id,
        :conditions => ['date <= ?', @datetime_end],
        :order => 'date desc')
        if @answer_sheet == nil
          @answer_sheet = AnswerSheet.new
          @answer_sheet.candidate_id = survey_candidate.candidate_id
        end
        @answer_sheets << @answer_sheet
      end
    end
  end
  def set_prefix_image(prefix)
    @prefix_image = prefix
  end
end

t = Time.now
datestr = t.strftime "%Y%m%d"
timestr = t.strftime "%H%M"
date = ARGV[0] || datestr
time = ARGV[1] || timestr
image_prefix = ARGV[2] || "."
outhtml_prefix = ARGV[3] || "."
from_mailaddr = ARGV[4] || "default-from"
to_mailaddr = ARGV[5] || "default-to"
faxuser = ARGV[6] || "default-user"
faxpass = ARGV[7] || "default-pass"
datetime = "#{date}#{time}"

if date !~ /\A\d{4}\d{2}\d{2}\z/
  STDERR.puts "date format must be #{datestr}"
  exit(1)
end

if time !~ /\A\d{2}\d{2}\d{2}\z/
  STDERR.puts "time format must be #{timestr}"
  exit(1)
end

config_db = "#{rails_prefix}/config/database.yml"
db_env = "development"
ActiveRecord::Base.configurations = YAML.load_file(config_db)
ActiveRecord::Base.establish_connection(db_env)
Dir.glob("#{Rails.root}/app/models/*.rb").each do |model|
  load model
end
Time::DATE_FORMATS[:date_nomal] = "%Y/%m/%d"
Time::DATE_FORMATS[:date_jp] = "%Y年%m月%d日"
Time::DATE_FORMATS[:datetime_jp] = "%Y年%m月%d日 %k時%M分"
Time::DATE_FORMATS[:time_jp] = "%k時%M分"
erb = File.open("#{Rails.root}/app/views/report/fax_preview.html.erb") {|f| ERB.new(f.read)}
erb.def_method(ReportHtml, 'render()', "#{Rails.root}/app/views/report/fax_preview.html.erb")

rep = ReportHtml.new
rep.set_datetime(datetime)
rep.set_prefix_image(image_prefix)

accept_survey_statuses = []
# survey is opened
accept_survey_statuses << 1

accept_sheet_statuses = []
# sheet is opened
accept_sheet_statuses << 1

groups = Group.all
groups.each do |group|
  print "#Group:#{group.group_name}\n"
  surveys = group.surveys.where(:status => accept_survey_statuses)
  next if surveys == nil
  surveys.each do |survey|
    print "#Survey:#{survey.survey_name}\n"
    next if !(survey.has_report_wday(rep.datetime.wday()) && survey.has_report(rep.datetime.strftime("%H"),rep.datetime.strftime("%M")))
    rep.set_survey_id(survey.id)
    filename = "#{outhtml_prefix}/#{datetime}_#{survey.id}.html"
    print filename
    File.open(filename,"w") {|file|
      file.write rep.render()
    }
    system("wkhtmltopdf --quiet --page-size A4 --orientation Landscape --encoding utf-8 #{filename} #{filename}.pdf")
    survey_candidates = survey.survey_candidates
    survey_candidates.each do |survey_candidate|
      next if !survey_candidate.has_sendreport_role
      print "#Candidate:#{survey_candidate.candidate.candidate_name}\n"
      fax_number = survey_candidate.candidate.fax_number
      fax_number = fax_number.gsub(/-/, '')
      print "sendfax #{fax_number} summary-report #{filename}.pdf\n"
      system("sendfax #{fax_number} summary-report #{filename}.pdf")
    end
    #system("rm #{filename}.pdf")
    #system("rm #{filename}")
  end
end
