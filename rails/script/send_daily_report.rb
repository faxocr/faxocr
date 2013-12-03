#!/usr/bin/env ruby
require File.expand_path('../../config/boot',  __FILE__)
rails_prefix = RAILS_ROOT
require "rubygems"
require "active_record"
require "yaml"
require "cgi"
require "erb"

class ReportHtml
  def initialize
    @survey = nil
    @answer_sheets = nil
    set_date("201001010000")
    @prefix_image = "."
    #@all_image = true
  end
  def set_date(date)
    @date = DateTime.strptime(str=date, fmt='%Y%m%d%H%M')
    @date_begin = @date.strftime("%Y/%m/%d 00:00:00")
    @date_end = @date.strftime("%Y/%m/%d 23:59:59")
    @place_holder = Hash.new
    @place_holder.store('YEAR', @date.strftime("%Y"))
    @place_holder.store('MONTH', @date.strftime("%m"))
    @place_holder.store('DAY', @date.strftime("%d"))
  end
  def date
    @date
  end
  def set_survey_id(survey_id)
    @survey = Survey.find(survey_id)
    @survey_candidates = @survey.survey_candidates
    sheet_ids = @survey.sheet_ids

    @answer_sheets = []
    @survey_candidates.each do |survey_candidate|
      if survey_candidate.has_receivereport_role
        @answer_sheet = AnswerSheet.find_by_sheet_id_and_candidate_id(sheet_ids, survey_candidate.candidate_id,
        :conditions => ['date <= ?', @date_end],
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
timestr = t.strftime "%Y%m%d%H%M"
date = ARGV[0] || timestr
image_prefix = ARGV[1] || "."
outhtml_prefix = ARGV[2] || "."
from_mailaddr = ARGV[3] || "default-from"
to_mailaddr = ARGV[4] || "default-to"
faxuser = ARGV[5] || "default-user"
faxpass = ARGV[6] || "default-pass"

config_db = rails_prefix + "/config/database.yml"
db_env = "development"
ActiveRecord::Base.configurations = YAML.load_file(config_db)
ActiveRecord::Base.establish_connection(db_env)
Dir.glob(RAILS_ROOT + '/app/models/*.rb').each do |model|
  load model
end
Time::DATE_FORMATS[:date_nomal] = "%Y/%m/%d"
Time::DATE_FORMATS[:date_jp] = "%Y年%m月%d日"
Time::DATE_FORMATS[:datetime_jp] = "%Y年%m月%d日 %k時%M分"
Time::DATE_FORMATS[:time_jp] = "%k時%M分"
erb = File.open(RAILS_ROOT + '/app/views/report/daily.html.erb') {|f| ERB.new(f.read)}
erb.def_method(ReportHtml, 'render()', RAILS_ROOT + '/app/views/report/daily.html.erb')

rep = ReportHtml.new
rep.set_date(date)
rep.set_prefix_image(image_prefix)

accept_survey_statuses = []
# survey is opened
accept_survey_statuses << 1

accept_sheet_statuses = []
# sheet is opened
accept_sheet_statuses << 1

groups = Group.find(:all)
groups.each do |group|
  print "#Group:#{group.group_name}\n"
  surveys = group.surveys.find_all_by_status(accept_survey_statuses)
  next if surveys == nil
  surveys.each do |survey|
    print "#Survey:#{survey.survey_name}\n"
    next if !(survey.has_report_wday(rep.date.wday()) && survey.has_report(rep.date.strftime("%H"),rep.date.strftime("%M")))
    rep.set_survey_id(survey.id)
    filename = "#{outhtml_prefix}/#{date}_#{survey.id}.html"
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

      #to the iFax
      system("echo '#userid=#{faxuser}' > #{filename}.pass")
      system("echo '#passwd=#{faxpass}' >> #{filename}.pass")
      print "sendemail -t #{fax_number}#{to_mailaddr} -u report -a #{filename}.pdf -o message-file=#{filename}.pass -f #{from_mailaddr}\n"
      system("sendemail -t #{fax_number}#{to_mailaddr} -u report -a #{filename}.pdf -o message-file=#{filename}.pass -f #{from_mailaddr}")

      system("rm #{filename}.pass")
    end
    #system("rm #{filename}.pdf")
    #system("rm #{filename}")
  end
end
