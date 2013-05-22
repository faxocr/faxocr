class ConfigsController < ApplicationController
  before_filter :administrator_only

  def index
    @config_file_path = "#{Rails.root}/../etc/faxocr.conf"
    @raw_config = File.read(@config_file_path)
  end

  def database
    @config_file_path = "#{Rails.root}/config/database.yml"
    @raw_config = File.read(@config_file_path)
  end

private
  def administrator_only
    unless @current_group.is_administrator?
      access_violation
    end
  end
end
