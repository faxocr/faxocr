class AddReportTimeToSurvey < ActiveRecord::Migration
  def self.up
  	add_column :surveys, :report_time, :time
  	add_column :surveys, :report_wday, :string
  end
  def self.down
  	remove_column :surveys, :report_time, :time
  	remove_column :surveys, :report_wday, :string
  end
end
